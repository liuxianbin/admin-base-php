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

use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Config;

class Upload {
    static $domain = '';

    public function index() {
        return $this->_upload(50000, "web");
    }

    function wangEditUpload() {
        return $this->_upload(5000, "web", null, true);
    }

    public function oss() {
        return $this->_aliyunOssUpload(50000);
    }

    private function _upload($max = 5000, $folder = NULL, $fname = NULL, $forWangEdit = false) {
        $typeArr = array("jpg", "png", "gif", "pdf", "jpeg", "wgt", "apk");
        if (isset($_POST)) {
            $name = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $name_tmp = $_FILES['file']['tmp_name'];
            if (empty($name)) {
                return json($this->_returnJsonArr(1, "您还未选择文件", [], $forWangEdit));
            }
            $type = strtolower(substr(strrchr($name, '.'), 1));
            if (!in_array($type, $typeArr)) {
                return json($this->_returnJsonArr(1, "清上传jpg,png或gif类型的图片", [], $forWangEdit));

            }
            if ($size > ($max * 1024)) {
                return json($this->_returnJsonArr(1, "大小已超过$max", [], $forWangEdit));
            }

            $dir = "/uploads/";
            if (empty($folder)) {
                $dir .= $folder . '/';
            }
            if (!file_exists(ROOT_PATH . $dir)) {
                mkdir(ROOT_PATH . $dir);
            }
            $pic_name = date('YmdHis', time()) . rand(10000, 99999) . "." . $type;
            if (!empty($fname)) {
                $pic_name = $fname . "." . $type;
            }
            if (move_uploaded_file($name_tmp, ROOT_PATH . $dir . $pic_name)) {
                if ($forWangEdit) {
                    return json($this->_returnJsonArr(0, "上传成功", [self::$domain . $dir . $pic_name], true));
                } else {
                    return json($this->_returnJsonArr(0, "上传成功", [
                        'src' => $dir . $pic_name,
                        'domain' => self::$domain
                    ]));
                }
            } else
                return json($this->_returnJsonArr(1, "上传有误，清检查服务器配置", [], $forWangEdit));
        }
    }


    private function _aliyunOssUpload($max = 5000, $forWangEdit = false) {
        if (isset($_POST)) {
            $name = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $name_tmp = $_FILES['file']['tmp_name'];
            if (empty($name)) {
                return json($this->_returnJsonArr(1, "您还未选择文件", [], $forWangEdit));
            }
            $type = strtolower(substr(strrchr($name, '.'), 1));
            if ($size > ($max * 1024)) {
                return json($this->_returnJsonArr(1, "大小已超过$max", [], $forWangEdit));
            }

            $pic_name = date('YmdHis', time()) . rand(10000, 99999) . "." . $type;
            if (!empty($fname)) {
                $pic_name = $fname . "." . $type;
            }
            Config::load('extra/app', "app");
            $cfg = Config::get("app.oss");
            $client = new OssClient($cfg["AccessKeyId"], $cfg["AccessKeySecret"], $cfg["Endpoint"]);
            $options = [
                OssClient::OSS_HEADERS => [
                    'x-oss-object-acl' => 'public-read',
                ]
            ];
            try {
                $info = $client->uploadFile($cfg["BucketName"], $pic_name, $name_tmp, $options);
                $ossUrl = $info["oss-request-url"];
                if (substr($ossUrl, 0, 4) == "http") {
                    $ossUrl = substr_replace($ossUrl, "https", 0, 4);
                }
                if ($forWangEdit) {
                    return json($this->_returnJsonArr(0, "上传成功", [$ossUrl], true));
                } else {
                    return json($this->_returnJsonArr(0, "上传成功", [
                        'src' => $ossUrl
                    ]));
                }
            } catch (OssException $e) {
                return json($this->_returnJsonArr(1, "上传有误，请检查服务器配置", [], $forWangEdit));
            }
        }
    }

    private function _returnJsonArr($code, $msg, $data = [], $forWangEdit = false) {
        if ($forWangEdit) {
            $row = [
                'errno' => $code,
                'data' => $data
            ];
        } else {
            $row = [
                'code' => $code,
                'msg' => $msg,
                'data' => $data
            ];
        }
        return $row;
    }
}