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

use app\admin\model\Account;
use think\facade\Config;
use think\facade\Session;

class Index {
    public function __construct() {
        $this->a = new Account();
    }

    function index() {
        if (Session::get(SESSION_UID) == null) {
            return redirect('/admin/login');
        }
        return view("home");
    }

    public function login() {
        if (request()->method() == "POST") {
            return $this->a->login(input('post.username'), input('post.password'));
        }
        return view('login');
    }

    public function logout() {
        Session::clear();
        return redirect('/admin/login');
    }

    // MENU_MODE_DB： true从数据库查询  false从配置文件读取
    function getMenu() {
        if (MENU_MODE_DB) {
            return $this->a->getMenu();
        }
        Config::load('extra/menu', "menu");
        $_list = Config::get("menu");
        return json([
            "list" => $_list,
            "init_url" => $_list[0]['data'][0]['url'],
            "init_opt" => $_list[0]['data']
        ]);
    }

    // 校验权限
    public function checkAuth() {
        return $this->a->checkAuth(input("post.controller"), input("post.action"), Session::get('rid'));
    }

    public function modifyPwd() {
        if (request()->method() == "POST") {
            return $this->a->modifyPwd(input("post.oldPass"), input("post.pass"));
        }
        return view('password');
    }

}