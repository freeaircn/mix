<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-06 01:17:02
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-07 22:39:56
 */

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;

class MyApiAuth
{
    // 400 请求错误
    // 401.1 用户没有登录
    // 401.3 用户没有权限
    // 500 服务端错误
    public function check(RequestInterface $request)
    {
        $url    = $request->uri->getSegments();
        $method = $request->getMethod();

        // 方法：登录，登出，重置密码，返回true
        if (isset($url[0]) && $url[0] === 'api' && isset($url[1]) && $url[1] === 'auth') {
            return ['status' => 0, 'code' => 0, 'msg' => ''];
        }

        // 检查是否存在session。存在 - 已登录。
        $phone = session('phone');
        if (is_null($phone)) {
            return ['status' => 401, 'code' => 1, 'msg' => '用户没有登陆'];
        }

        // ！！！超级用户，仅测试使用
        // if ($phone == '13812345678') {
        //     return ['status' => 0, 'code' => 0, 'msg' => ''];
        // }

        // API
        $allowApi = session('allowApi');
        if (is_null($allowApi) || empty($allowApi)) {
            return ['status' => 401, 'code' => 3, 'msg' => '用户没有权限'];
        }
        $request = '';

        if (isset($url[0]) && $url[0] === 'api') {
            for ($i = 1; $i < count($url); $i++) {
                $request = $request . $url[$i] . '/';
            }
            //
            if ($method === 'get') {
                $request = rtrim($request, "/") . ':' . 'read';
            }
            if ($method === 'post') {
                $request = rtrim($request, "/") . ':' . 'write';
            }
            if ($method === 'put') {
                $request = rtrim($request, "/") . ':' . 'write';
            }
            if ($method === 'delete') {
                $request = rtrim($request, "/") . ':' . 'write';
            }
        }

        if (in_array($request, $allowApi) === false) {
            return ['status' => 401, 'code' => 3, 'msg' => '用户没有权限'];
        }

        return ['status' => 0, 'code' => 0, 'msg' => ''];
    }
}
