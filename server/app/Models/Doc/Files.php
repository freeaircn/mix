<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-19 00:18:20
 */

namespace App\Models\Doc;

use CodeIgniter\Model;

class Files extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'category_id', 'associated_id', 'user_id', 'file_org_name', 'file_new_name', 'file_ext', 'file_mime_type', 'size', 'path', 'created_at', 'updated_at', 'deleted_at'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'doc_files';
        parent::__construct();
    }

    public function insertMultiRecords(array $records = null)
    {
        if (empty($records)) {
            return false;
        }

        return $this->insertBatch($records);
    }

}
