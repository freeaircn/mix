<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-06 21:44:27
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-26 23:19:53
 */

namespace Config\MyConfig;

use CodeIgniter\Config\BaseConfig;

class DocStore extends BaseConfig
{
    public $filePath           = 'uploads/drawing';
    public $tempPath           = 'uploads/temp';
    public $maxFileSize        = 104857600; // 100*1024*1024
    public $fileExceedSizeMsg  = '附件大小超过 100MB'; // 100*1024*1024
    public $fileInvalidTypeMsg = '允许文件类型 pdf, zip';
    public $allowedFileTypes   = [
        'application/zip',
        'application/pdf',
    ];
    // 2023-2-26
    public $CATEGORY;
    public $SECRET_LEVEL;
    public $RETENTION_PERIOD;
    //
    public $PARTY_BRANCH_CODE = '01';

    public function __construct()
    {
        parent::__construct();

        // 注意：下标为站点 station_id
        $this->CATEGORY[0] = [];
        $this->CATEGORY[1] = [];
        $this->CATEGORY[2] = [
            ['id' => 1, 'pid' => 0, 'name' => '顶层', 'code' => ''],
            ['id' => 2, 'pid' => 1, 'name' => '党支部', 'code' => '01'],
            ['id' => 3, 'pid' => 2, 'name' => '支部风采', 'code' => '0101'],
            ['id' => 4, 'pid' => 2, 'name' => '主题教育', 'code' => '0102'],
            ['id' => 5, 'pid' => 2, 'name' => '党务公开', 'code' => '0103'],
            ['id' => 6, 'pid' => 2, 'name' => '会议纪要', 'code' => '0104'],
            ['id' => 7, 'pid' => 2, 'name' => '学习园地', 'code' => '0105'],
        ];

        //
        $this->SECRET_LEVEL = [
            ['id' => 10, 'name' => '公开', 'code' => 10],
            ['id' => 20, 'name' => '内部公开', 'code' => 20],
            ['id' => 30, 'name' => '秘密', 'code' => 30],
            ['id' => 40, 'name' => '机密', 'code' => 40],
            ['id' => 50, 'name' => '绝密', 'code' => 50],
        ];
        $this->RETENTION_PERIOD = [
            ['id' => 10, 'name' => '10年', 'code' => 10],
            ['id' => 30, 'name' => '30年', 'code' => 30],
            ['id' => 99, 'name' => '永久', 'code' => 99],
        ];
    }
}
