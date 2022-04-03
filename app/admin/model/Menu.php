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
        if (!is_numeric($fid)) {
            //新增
        } else {
            //查询id是否存在
            //不存在则新增
        }
        // 二级菜单
        $menu_id = $input['menu_id'];
        $menu_name = $input['menu_name'];
        if (empty($menu_id)) {
            // 新增
        }
        // 查询变更二级菜单名称

        // 控制器
        $curr_controller = $input['curr_controller'];
        // 方法
        $curr_actions = $input['curr_actions'];
        // 遍历
        // 判断新增or更新
        return json($input);
    }


}