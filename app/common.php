<?php

use think\facade\Db;

if (!function_exists('ajax_result')) {
    function ajax_result($res = true, $msg = "", $data = []) {
        $_data = ['status' => $res ? SUCCESS : FAIL, 'message' => $msg];
        empty($data) or $_data['data'] = $data;
        if ($res === 0) {
            $_data['status'] = NOCHANGE;
            if (empty($msg)) {
                $_data['message'] = '数据没有变化';
            }
            return json($_data);
        }
        empty($msg) && $_data['message'] = $res ? '成功' : '系统繁忙,请稍后重试';
        return json($_data);
    }
}

if (!function_exists('header_access')) {
    function header_access() {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:*');
    }
}

if (!function_exists('filterInvalidData')) {
    function filterInvalidData($input, $fields) {
        if (is_string($fields)) {
            $fields = Db::getTableInfo($fields, 'fields');
        }
        $diff = array_diff(array_keys($input), $fields);
        foreach ($diff as $key => $value) {
            unset($input[$value]);
        }
        return $input;
    }
}
if (!function_exists("default_page_data")) {
    function default_page_data() {
        return [
            'curr' => input('param.curr') ?: 1,
            'limit' => input('param.limit') ?: PERPAGE
        ];
    }
}

defined("SUCCESS") or define("SUCCESS", "success");
defined("FAIL") or define("FAIL", "error");
defined("NOCHANGE") or define("NOCHANGE", "nochange");
defined("PERPAGE") or define("PERPAGE", 15);
defined("ADMIN") or define("ADMIN", "admin");
defined("SESSION_UID") or define("SESSION_UID", "site_uid");
// MENU_MODE_DB： true从数据库查询  false从配置文件读取
defined("MENU_MODE_DB") or define("MENU_MODE_DB", true);
