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
        $list = Db::query("select id,name,fid,level from clue_menu where is_check=1 and status=1 and is_delete=0 order by level,id");
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


}