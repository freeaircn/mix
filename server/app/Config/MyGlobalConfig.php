<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-29 10:42:03
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
    // public $userDeptLevel = 2; // 部门组织为树形结构，页面显示用户的2级部门名称。

    /**
     * 头像
     */
    public $defaultAvatarPath   = 'avatar/default/';
    public $defaultAvatarMale   = 'male.jpg';
    public $defaultAvatarFemale = 'female.jpg';

    /**
     * DTS 工作流配置文件
     */
    public $dtsKeyValuePairs = [
        'type'  => [
            1 => '隐患',
            2 => '故障',
        ],
        'level' => [
            1 => '紧急',
            2 => '严重',
            3 => '一般',
        ],
    ];
    public $wfDtsConfigFile             = 'Libraries/Workflow/Dts/config.yaml';
    public $dtsAttachmentPath           = 'uploads/dts';
    public $dtsAttachmentSize           = 8388608; // 8*1024*1024
    public $dtsAttachmentExceedSizeMsg  = '附件大小超过限制 8MB'; // 8*1024*1024
    public $dtsAttachmentInvalidTypeMsg = '允许文件类型: jpg, png, txt, pdf, doc, docx, xls, xlsx, ppt, pptx, zip';
    public $dtsAttachmentFileTypes      = [
        'image/jpeg',
        'image/png',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/pdf',
    ];
    public $dtsProgressTemplates = [
        'new_form'        => "【现象】\n\n【时间】\n\n【影响】\n\n【已采取措施】\n\n",
        'update_progress' => "【当前进展】\n\n【下一步计划】\n\n",
        'to_resolve'      => "【现象】\n\n【影响】\n\n【根因】\n\n【恢复方法】\n\n",
        'to_close'        => "【审核意见】\n\n",
        'back_work'       => "【重新处理意见】\n\n",
    ];
}
