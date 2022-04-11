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

use think\facade\Db;
use think\facade\Session;

class Base {
    protected $table;
    protected $IsPOST = false;
    protected $uid;

    public function __construct($table) {
        $this->uid = Session::get(SESSION_UID);
        $this->table = $table;
        $this->IsPOST = request()->method() == "POST";
    }

    private function New() {
        return Db::table($this->table);
    }

    public function Row() {
        return json($this->New()->where("id", input("post.id"))->find());
    }

    public function Delete() {
        return ajax_result($this->New()->where("id", input("post.id"))->update(["is_delete" => 1]));
    }

    public function DeleteReal() {
        return ajax_result($this->New()->where("id", input("post.id"))->delete());
    }

    public function Save($data, $field) {
        $data = filterInvalidData($data, $field);
        $id = intval($data["id"]);
        if (empty($id)) {
            $res = $this->New()->insert($data);
        } else {
            $res = $this->New()->where("id", $id)->update($data);
        }
        return ajax_result($res);
    }
}