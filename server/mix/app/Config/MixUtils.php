<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-15 22:15:43
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
    public $lockoutTime = 600;

    /**
     * Stores self-define rules.
     *
     */
    // public $validationRules = [
    //     'phone'    => 'required',
    //     'password' => 'required',
    //     'email'    => 'required|valid_email',
    // ];

}
