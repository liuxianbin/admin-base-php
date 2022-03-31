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
namespace app\admin;

use app\admin\model\Account;
use think\facade\Session;

class Auth {
    public function handle($request, \Closure $next) {
        $pathInfo = $request->server('REQUEST_URI');
        if (stripos($pathInfo, "/api/") === 0) {
            return $next($request);
        }
        $exclude = require __DIR__ . DIRECTORY_SEPARATOR . 'exclude.php';
        if (!in_array($pathInfo, $exclude)) {
            $is_post = request()->method() == "POST";
            if (Session::get(SESSION_UID) == null) {
                if ($is_post) {
                    $this->showJsonError();
                } else {
                    echo "<script>window.top.location.href='/admin/login';</script>";
                    exit;
                }
            }
            $arr = explode("/", $pathInfo);
            if (count($arr) > 2) {
                $controller = $arr[2] ?: "index";
                $action = "index";
                isset($arr[3]) && $action = $arr[3];
                if (stripos($action, "?") !== false) {
                    $action = strchr($action, "?", true);
                }
                $this->check($controller, $action);
            }
        }
        return $next($request);
    }

    private function check($controller, $action) {
        $u = new Account();
        $rid = intval(Session::get("rid"));
        $u->checkAuth($controller, $action, $rid) or $this->showError();
    }

    function showPageError() {
        include __DIR__ . "/404.html";
        exit;
    }

    function showJsonError() {
        $msg = "You don't have permission to access";
        echo json_encode([
            'status' => FAIL,
            'data' => [
                "msg" => $msg
            ],
            'message' => $msg
        ]);
        exit;
    }

    function showError() {
        if (request()->method() == "POST") {
            $this->showJsonError();
        }
        $this->showPageError();
    }
}