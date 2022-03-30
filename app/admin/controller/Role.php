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

class Role extends Base {
    public function __construct() {
        parent::__construct("sys_role");
        $this->r = new \app\admin\model\Role();
    }

    public function roleOptions() {
        return $this->r->roleOptions();
    }

    public function list() {
        if ($this->IsPOST) {
            return $this->r->list();
        }
        return view("role_list");
    }

    public function edit() {
        if ($this->IsPOST) {
            return $this->r->edit_role(input("post."));
        }
        return view("role_edit", ["id" => intval(input("param.id"))]);
    }

    public function changeStatus() {
        return $this->r->changeStatus(input("post.id"), input("post.status"));
    }

}
