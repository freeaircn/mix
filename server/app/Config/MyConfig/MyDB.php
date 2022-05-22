<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 18:19:19
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class MyDB extends BaseConfig
{
    /**
     * 数据库
     */
    public $dbName   = 'mix';
    public $dbPrefix = 'app_';
}
