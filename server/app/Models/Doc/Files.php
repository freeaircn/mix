<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-04-22 16:05:22
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

    public function insertOneRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
        }

        return $this->insert($data);
    }

    public function insertMultiRecords(array $records = null)
    {
        if (empty($records)) {
            return false;
        }

        return $this->insertBatch($records);
    }

    public function getByAssociatedID(array $fields = null, $associated_id = null)
    {
        if (empty($associated_id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, category_id, associated_id, user_id, file_org_name, file_new_name, file_ext, file_mime_type, size, path, created_at, deleted_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $selectSql = trim($selectSql, ', ');

        $builder = $this->select($selectSql);
        $builder->where('associated_id', $associated_id);
        $res = $builder->findAll();

        if (empty($res)) {
            return [];
        } else {
            return $res;
        }
    }

    public function getByID(array $fields = null, $id = null)
    {
        if (empty($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, category_id, associated_id, user_id, file_org_name, file_new_name, file_ext, file_mime_type, size, path, created_at, deleted_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $selectSql = trim($selectSql, ', ');

        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $res = $builder->findAll();

        if (empty($res)) {
            return [];
        } else {
            return $res[0];
        }
    }

    public function delByID(string $id = null)
    {
        if (empty($id)) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }

        $result = $this->where('id', $id)->delete();

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public function deleteByAssociatedID(string $associated_id = null)
    {
        if (empty($associated_id)) {
            return false;
        }

        if (!is_numeric($associated_id)) {
            return false;
        }

        $result = $this->where('associated_id', $associated_id)->delete();

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

}
