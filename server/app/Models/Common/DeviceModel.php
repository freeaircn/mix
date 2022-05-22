<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:42:53
 */

namespace App\Models\Common;

use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'pid', 'description'];

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
        $this->table   = $config->dbPrefix . 'device';
        parent::__construct();
    }

    public function getDeviceAllRecords(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, description, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }

    public function getDeviceRecordsExcludeFirst(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, description, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->where(['id > ' => 1]);
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }

    public function getDeviceRecordsByIds(array $fields = null, array $ids = null)
    {
        if (empty($ids)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, description, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->whereIn('id', $ids);
        $builder->orderBy('id', 'ASC');
        $res = $builder->findAll();

        return $res;
    }

    // public function get($fields = [], $query = [])
    // {
    //     if (empty($fields)) {
    //         return [];
    //     }

    //     $selectSql = '';
    //     foreach ($fields as $key) {
    //         $selectSql = $selectSql . $key . ', ';
    //     }
    //     $builder = $this->select($selectSql);
    //     $builder->where($query);
    //     $builder->orderBy('id', 'ASC');

    //     $res = $builder->findAll();

    //     return $res;
    // }
}
