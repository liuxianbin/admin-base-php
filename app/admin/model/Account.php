<?php
// +----------------------------------------------------------------------
// | admin-base-php
// +----------------------------------------------------------------------
// | Copyright (c) 2022 liuxianbin
// +----------------------------------------------------------------------
// | Licensed ( MIT License )
// +----------------------------------------------------------------------
// | Author: https://github.com/liuxianbin
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\facade\Db;
use think\facade\Session;

class Account {
    public function __construct() {
        $this->table = 'sys_account';
        $this->t_menu = "sys_menu";
        $this->t_role = "sys_role";
    }

    public function db() {
        return Db::table($this->table);
    }

    public function login($username, $password) {
        $row = $this->db()->where(['username' => $username])->find();
        if (empty($row)) {
            return ajax_result(false, '用户不存在');
        }
        if ($row['password'] != MD5(sha1(base64_encode($password) . $row['sign']))) {
            return ajax_result(false, '账号或密码错误');
        }
        Session::set(SESSION_UID, $row["id"]);
        Session::set('uname', $row['username']);
        Session::set('rid', $row['role_id']);
        return ajax_result(true);
    }

    public function modifyPwd($oldPwd, $password) {
        $uid = Session::get(SESSION_UID);
        $row = $this->db()->field('sign,password')->where(['id' => $uid])->find();
        if ($row['password'] != MD5(sha1(base64_encode($oldPwd) . $row['sign']))) {
            return ajax_result(false, '旧密码错误');
        }
        $sign = mt_rand(1000, 9999);
        $res = $this->db()->where('id', $uid)->update([
            'sign' => $sign,
            'password' => MD5(sha1(base64_encode($password) . $sign))
        ]);
        return ajax_result($res);
    }

    public function editAccount($data) {
        $raw_pwd = $data["pwd_raw"];
        $data = filterInvalidData($data, $this->table);
        $id = intval($data['id']);
        $row = $this->db()->field("id")->where("username", $data["username"])->find();
        if (!empty($row) && $row["id"] != $id) {
            return ajax_result(false, '账号已存在');
        }
        if ($raw_pwd != $data["password"]) {
            $data["sign"] = mt_rand(100000, 999999);
            $data["password"] = MD5(sha1(base64_encode($data["password"]) . $data['sign']));
        }
        if (empty($id)) {
            $res = $this->db()->insert($data);
        } else {
            $res = $this->db()->where("id", $id)->update($data);
        }
        return ajax_result($res);
    }

    public function list() {
        $data = default_page_data();
        $where = " c.is_delete=0 and c.role_id<>0 ";
        $param = [];
        $username = input("post.username");
        if (!empty($username)) {
            $where .= " AND c.username like ?";
            $param[] = "%" . $username . "%";
        }
        $sql_count = "SELECT count(1) num FROM {$this->table} c WHERE $where ORDER BY c.id DESC";
        $data['count'] = Db::query($sql_count, $param)[0]["num"];
        $param = array_merge($param, [$data['limit'], $data['limit'] * ($data['curr'] - 1)]);
        $sql = "SELECT c.*,r.role_name FROM {$this->table} c JOIN sys_role r on r.id=c.role_id WHERE $where ORDER BY c.id DESC LIMIT ? OFFSET ?";
        $list = Db::query($sql, $param);
        $data['list'] = $list;
        return json($data);
    }

    // 校验权限
    function checkAuth($controller, $action, $rid = 0) {
        $controller = strtolower($controller);
        $action = strtolower($action);
        if (Session::get('uname') == ADMIN) {
            return TRUE;
        }
        // 平台超级管理员
        if ($rid === 0) {
            return TRUE;
        }
        $row = Db::query("select id,is_check,status from {$this->t_menu} where is_delete=0 and controller=? and action=? limit 1", [$controller, $action]);
        if (empty($row)) {
            return FALSE;
        }
        if (!$row[0]['is_check']) {
            return TRUE;
        }
        if (!$row[0]['status']) {
            return FALSE;
        }
        $row = Db::query("SELECT id FROM {$this->t_role} WHERE id = ? AND FIND_IN_SET(?, menus)", [$rid, $row[0]["id"]]);
        if (empty($row)) {
            return FALSE;
        }
        return TRUE;
    }

    // 获取菜单
    public function getMenu() {
        if (Session::get('uname') == ADMIN) {
            $sql = "SELECT m.* FROM {$this->t_menu} m WHERE m.status = 1 AND m.is_display = 1 AND m.is_delete=0 ORDER BY m.level, m.sort_num DESC,m.id DESC";
        } else {
            $rid = Session::get("rid");
            $sql = "
				SELECT m.*
				FROM {$this->t_role} r
				JOIN {$this->t_menu} m ON FIND_IN_SET(m.id, CONCAT_WS(IF(r.half_check_menus,',',''),r.half_check_menus,r.menus))
				WHERE r.id = $rid
				AND r.status = 1
				AND r.is_delete = 0
				AND m.status = 1
                AND m.is_display = 1
				AND m.is_delete = 0
				ORDER BY
					m.level,
					m.sort_num DESC,
					m.id DESC
			";
        }
        $list = Db::query($sql);
        foreach ($list as $k => $v) {
            if ($v['module'] == '--' or $v["action"] == '--') {
                $list[$k]["url"] = "javascript:;";
            } else
                $list[$k]["url"] = '/' . $v['module'] . '/' . $v["controller"] . "/" . $v["action"];
        }
        $m = new Menu();
        // _Build无限分级
        $_list = $m->_Build(0, $list);
        if (empty($_list)) {
            return json([
                "list" => [],
                // 默认打开页面
                "init_url" => "javascript:;",
                // 默认左侧菜单
                "init_opt" => []
            ]);
        }
        $init_child = $_list[0]['children'];
        return json([
            "list" => $_list,
            "init_url" => empty($init_child) ? "javascript:;" : $init_child[0]["url"],
            "init_opt" => $init_child
        ]);
    }


}