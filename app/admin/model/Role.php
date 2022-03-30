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

class Role {
    public function __construct() {
        $this->table = 'sys_role';
    }

    public function db() {
        return Db::table($this->table);
    }

    public function roleOptions() {
        return json($this->db()->field("id,role_name")->where("is_delete", 0)->where("status", 1)->order("id")->select());
    }

    public function list() {
        $data = default_page_data();
        $where = [
            ["is_delete", '=', 0],
        ];
        $roleName = input("post.role_name");
        if (!empty($roleName)) {
            $where[] = ["role_name", "like", '%' . $roleName . '%'];
        }
        $data['count'] = $this->db()->where($where)->count();
        $data["list"] = $this->db()->where($where)->order("id")->limit($data['limit'] * ($data['curr'] - 1), $data['limit'])->select();
        return json($data);
    }

    public function changeStatus($id, $status) {
        return ajax_result($this->db()->where('id', $id)->update(["status" => $status]));
    }

    public function edit_role($data) {
        $data = filterInvalidData($data, $this->table);
        $id = intval($data['id']);
        if (empty($id)) {
            $res = $this->db()->insert($data);
        } else {
            $res = $this->db()->where("id", $id)->update($data);
        }
        return ajax_result($res);
    }


}