<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:38:40
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    /**
     * 登录
     */
    public $maxAttempts = 5;
    public $lockoutTime = 600; // 秒
    // public $userDeptLevel = 2; // 部门组织为树形结构，页面显示用户的2级部门名称。
}
