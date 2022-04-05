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

class Menu {
    public function __construct() {
        $this->table = 'sys_menu';
    }

    public function db() {
        return Db::table($this->table);
    }

    // 角色权限选项
    // 有效 && 不校验
    public function roleOption() {
        $list = Db::query("select id,name,fid,level from $this->table where is_check=1 and status=1 and is_delete=0 order by level,id");
        $_list = $this->_Build(0, $list);
        return json($_list);
    }

    // 处理成element-tree格式
    function _Build($fid, &$list) {
        $_list = [];
        $child = $this->_GetChild($fid, $list);
        foreach ($child as $k => $v) {
            $_list[] = [
                "id" => $v["id"],
                "label" => $v["name"],
                "children" => $this->_Build($v["id"], $list)
            ];
        }
        return $_list;
    }

    function _GetChild($fid, &$list) {
        $_list = [];
        foreach ($list as $k => $v) {
            if ($v["fid"] == $fid) {
                $_list[] = ["id" => $v["id"], "name" => $v["name"]];
                unset($list[$k]);
            }
        }
        return $_list;
    }

    // 一级菜单
    public function getSuperMenu() {
        $list = Db::query("select id,name from $this->table where fid=0 and level=1 and status=1 and is_delete=0 order by sort_num desc,id");
        return json($list);
    }

    public function edit($input) {
        $fid = $input["menu_fid"];
        // 顶层菜单
        $fid_param = [
            'name' => $fid,
            'is_display' => 1,
            'level' => 1,
            'fid' => 0,
            'module' => '--',
            'controller' => '--',
            'action' => '--',
        ];
        if (!is_numeric($fid)) {
            $fid = Db::table($this->table)->insertGetId($fid_param);
        } else {
            //查询fid是否存在
            $row = Db::table($this->table)->where('id', $fid)->find();
            if (empty($row)) {
                $fid = Db::table($this->table)->insertGetId($fid_param);
            }
        }
        // 二级菜单
        $menu_id = $input['menu_id'];
        $menu_name = $input['menu_name'];
        if (empty($menu_id)) {
            $param = $fid_param;
            $param['level'] = 2;
            $param['fid'] = $fid;
            $param['name'] = $menu_name;
            $param['controller'] = $input['curr_controller'];
            $fid = Db::table($this->table)->insertGetId($param);
        } else {
            Db::table($this->table)->where('id', $menu_id)->update([
                'name' => $menu_name
            ]);
        }
        $curr_actions = $input['curr_actions'];
        foreach ($curr_actions as $item) {
            if (empty($item["id"])) {
                $item["level"] = 3;
                $item["fid"] = $fid;
                Db::table($this->table)->insert($item);
            } else {
                Db::table($this->table)->where('id', $item["id"])->update($item);
            }
        }
        return ajax_result(true);
    }

    public function getActions($controller) {
        $row = Db::table($this->table)->field("id as menu_id,name as menu_name,fid as menu_fid")->where("controller", $controller)->where("level", "2")->find();
        if (empty($row)) {
            $row = [
                "menu_id" => '',
                "menu_name" => '',
                "menu_fid" => '',
                "actions" => []
            ];
        } else {
            $row["actions"] = Db::table($this->table)->where("fid", $row["menu_id"])->select()->toArray();
        }
        return $row;
    }


}