<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-20 21:37:54
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class Throttler extends BaseConfig
{

    public $api_sms_throttle = [
        'capacity' => 3,
        'seconds'  => 60,
    ];
}
