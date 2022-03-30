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


class Account extends Base {
    public function __construct() {
        parent::__construct("sys_account");
        $this->a = new \app\admin\model\Account();
    }

    public function edit() {
        if (request()->method() == "POST") {
            return $this->a->edit_account(input("post."));
        }
        return view("account_edit", ["id" => intval(input("param.id"))]);
    }

    public function list() {
        if ($this->IsPOST) {
            return $this->a->list();
        }
        return view("account_list");
    }

}