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

    public function edit_account($data) {
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

}