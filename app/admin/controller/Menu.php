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
namespace app\admin\controller;

class Menu extends Base {
    public function __construct() {
        parent::__construct("sys_menu");
        $this->m = new \app\admin\model\Menu();
    }

    // 角色权限选项
    public function roleOption() {
        return $this->m->roleOption();
    }

    public function index() {
        return view("menu_list");
    }

    public function getActions() {
        $controller = input("post.controller");
        $name = 'app\admin\controller\\' . $controller;
        $arr = get_class_methods(new $name());
        foreach ($arr as $k => $v) {
            if ($v == '__construct') {
                unset($arr[$k]);
            }
        }
        $_list = [];
        foreach ($arr as $item) {
            $_list[] = [
                "module" => "admin",
                "controller" => $controller,
                "action" => $item,
                "display_type" => 1,
                "is_check" => 1,
                "status" => 1,
                "sort_num" => 1
            ];
        }
        return json($_list);
    }

    function getControllers() {
        $pathList = glob(APP_PATH . "admin/controller/*.php");
        $list = [];
        foreach ($pathList as $key => $value) {
            $_value = basename($value, '.php');
            if ($_value != 'Base') {
                $list[] = $_value;
            }
        }
        return json($list);
    }
}
