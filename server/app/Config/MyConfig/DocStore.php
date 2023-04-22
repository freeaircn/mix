<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2023-04-21 11:25:27
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class DocStore extends BaseConfig
{
    public $tempPath           = 'uploads/temp';
    public $maxFileSize        = 104857600; // 100*1024*1024
    public $fileExceedSizeMsg  = '附件大小超过 100MB'; // 100*1024*1024
    public $fileInvalidTypeMsg = '允许文件类型 pdf, zip';
    public $allowedFileTypes   = [
        'application/zip',
        'application/pdf',
    ];
    public $STATUS = [
        'published',
        'draft',
        'deleted',
    ];
    // 2023-2-26
    public $SECRET_LEVEL;
    public $RETENTION_PERIOD;
    //
    public $partyBranch;

    public function __construct()
    {
        parent::__construct();
        //
        $this->SECRET_LEVEL = [
            ['id' => '10', 'name' => '公开', 'code' => '10'],
            ['id' => '20', 'name' => '内部公开', 'code' => '20'],
            ['id' => '30', 'name' => '秘密', 'code' => '30'],
            ['id' => '40', 'name' => '机密', 'code' => '40'],
            ['id' => '50', 'name' => '绝密', 'code' => '50'],
        ];
        $this->RETENTION_PERIOD = [
            ['id' => '3', 'name' => '3年', 'code' => '3'],
            ['id' => '5', 'name' => '5年', 'code' => '5'],
            ['id' => '10', 'name' => '10年', 'code' => '10'],
            ['id' => '30', 'name' => '30年', 'code' => '30'],
            ['id' => '99', 'name' => '永久', 'code' => '99'],
        ];
        //
        $this->partyBranch = [
            'filePath'         => 'uploads/party_branch',
            'allowedFileTypes' => [
                'application/zip',
                'application/pdf',
            ],
        ];
    }
}
