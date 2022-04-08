<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-06 01:17:02
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-08 15:33:13
 */

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;

class MyApiAuth
{
    public function check(RequestInterface $request)
    {
        $url = $request->uri->getSegments();

        // 方法：登录，登出，重置密码，返回true
        if (isset($url[0]) && $url[0] === 'api' && isset($url[1]) && $url[1] === 'auth') {
            return true;
        }

        // 检查是否存在session。存在 - 已登录。
        $phone = session('phone');
        if (is_null($phone)) {
            return '用户未登录';
        }

        // ！！！超级用户，仅测试使用。
        // if ($phone == '13812345679') {
        //     return true;
        // }

        // API
        $allowApi = session('allowApi');
        if (is_null($allowApi) || empty($allowApi)) {
            return '用户没有权限';
        }
        $wanted = '';
        $method = $request->getMethod();

        if (isset($url[0]) && $url[0] === 'api') {
            for ($i = 1; $i < count($url); $i++) {
                $wanted = $wanted . $url[$i] . '/';
            }
            $wanted = rtrim($wanted, "/") . ':' . $method;
        }

        if (in_array($wanted, $allowApi) === false) {
            return '用户没有权限';
        }

        // 数据
        // $stationInSession = session('ownDirectDataDeptId');
        // $stationInRequest = isset($_POST['station_id']) ? $_POST['station_id'] : null;
        // if ($stationInRequest !== null) {
        //     if ($stationInRequest != $stationInSession) {
        //         return '没有访问权限';
        //     }
        // }
        // $stationInRequest = isset($_GET['station_id']) ? $_GET['station_id'] : null;
        // if ($stationInRequest !== null) {
        //     if ($stationInRequest != $stationInSession) {
        //         return '没有访问权限';
        //     }
        // }

        return true;
    }
}
