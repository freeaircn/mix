<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-11 10:09:55
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class DtsMetaModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'meta_id';
    protected $allowedFields = ['dts_id', 'meta_key', 'meta_value'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'dtsmeta';
        parent::__construct();
    }

}
