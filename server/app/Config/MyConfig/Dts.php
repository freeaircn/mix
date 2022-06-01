<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-01 22:58:47
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class Dts extends BaseConfig
{
    // public $keyValuePairs = [
    //     'type'  => [
    //         1 => '隐患',
    //         2 => '缺陷',
    //     ],
    //     'level' => [
    //         1 => '紧急',
    //         2 => '严重',
    //         3 => '一般',
    //     ],
    // ];
    public $typesMap = [
        1 => '隐患',
        2 => '缺陷',
    ];

    public $levelsMap = [
        1 => '紧急',
        2 => '严重',
        3 => '一般',
    ];

    public $wfDtsConfigFile          = 'Libraries/Workflow/Dts/config.yaml';
    public $attachmentPath           = 'uploads/dts';
    public $attachmentSize           = 8388608; // 8*1024*1024
    public $attachmentExceedSizeMsg  = '附件大小超过限制 8MB'; // 8*1024*1024
    public $attachmentInvalidTypeMsg = '允许文件类型: jpg, png, txt, pdf, doc, docx, xls, xlsx, ppt, pptx, zip';
    public $attachmentFileTypes      = [
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
    public $progressTemplates = [
        'new_form'        => "【现象】\n\n【时间】\n\n【影响】\n\n【已采取措施】\n\n",
        'update_progress' => "【当前进展】\n\n【下一步计划】\n\n",
        'to_suspend'      => "【挂起原因】\n\n",
        'to_cancel'       => "【取消原因】\n\n",
        'to_resolve'      => "【现象】\n\n【影响】\n\n【恢复方法】\n\n",
        'to_close'        => "【关闭审核意见】\n\n",
        'back_work'       => "【重新处理原因】\n\n",
    ];
    public $causes = [
        ['id' => '10', 'name' => '机械故障'],
        ['id' => '20', 'name' => '电路故障'],
        ['id' => '30', 'name' => '软件异常'],
        ['id' => '40', 'name' => '老化失效'],
        ['id' => '50', 'name' => '人为原因'],
        ['id' => '60', 'name' => '不可抗力'],
    ];

    public $longTermDays = 90;
    public $newsItemNum  = 8;

    public $dtsDetailsLink = "http://192.168.1.100/dashboard/dts/details/";

    public $cachePrefix = [
        'statistic_chart' => ['prefix' => 'dts_statistic_chart'],
    ];
}
