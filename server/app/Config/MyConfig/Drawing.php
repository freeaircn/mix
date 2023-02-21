<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-21 19:12:24
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class Drawing extends BaseConfig
{
    public $filePath           = 'uploads/drawing';
    public $maxFileSize        = 104857600; // 100*1024*1024
    public $fileExceedSizeMsg  = '附件大小超过 100MB'; // 100*1024*1024
    public $fileInvalidTypeMsg = '允许文件类型 pdf, zip';
    public $allowedFileTypes   = [
        'application/zip',
        'application/pdf',
    ];
}
