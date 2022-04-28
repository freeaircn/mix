<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:02:42
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-28 17:40:08
 */

namespace Config;

use App\Libraries\MyUtils;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    // public static function example($getShared = true)
    // {
    //     if ($getShared)
    //     {
    //         return static::getSharedInstance('example');
    //     }
    //
    //     return new \CodeIgniter\Example();
    // }

    // public static function MyUtils($getShared = true)
    // {
    //     if ($getShared) {
    //         return static::getSharedInstance('MyUtils');
    //     }

    //     return new MyUtils();
    // }

    public static function MyUtils($config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('MyUtils', $config);
        }

        if (empty($config) || !(is_array($config) || $config instanceof MyUtils)) {
            $config = config('MyUtils');
        }

        return new MyUtils($config);
    }
}
