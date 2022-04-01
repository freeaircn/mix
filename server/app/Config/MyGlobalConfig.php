<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 19:35:56
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MyGlobalConfig extends BaseConfig
{
    /**
     * 数据库
     */
    public $dbName   = 'mix';
    public $dbPrefix = 'app_';

    /**
     * 登录
     */
    public $maxAttempts = 5;
    public $lockoutTime = 600; // 秒

    /**
     * 默认头像
     */
    public $defaultAvatarPath   = 'avatar/default/';
    public $defaultAvatarMale   = 'male.jpg';
    public $defaultAvatarFemale = 'female.jpg';

    /**
     * DTS 工作流配置文件
     */
    public $wfDtsConfigFile = 'Libraries/Workflow/Dts/config.yaml';
}
