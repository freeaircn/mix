<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-18 15:21:04
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MixUtils extends BaseConfig
{
    /**
     * Password hash
     */
    public $maxPwdSizeBytes     = 254;
    public $argon2DefaultParams = [
        'memory_cost' => 1 << 14, // 16MB
        'time_cost'   => 4,
        'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    /**
     * 登录
     */
    public $maxAttempts = 5;
    public $lockoutTime = 600; // 秒

    /**
     * 验证码
     */
    public $smsCodeTimeout = 600; // 秒

}
