<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-27 20:56:43
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class ApiModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['type', 'pid', 'title', 'api', 'method'];

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
        $this->table   = $config->dbPrefix . 'api';
        parent::__construct();
    }

    public function getApiRecordsByIds(array $fields = null, array $Ids = null)
    {
        if (empty($Ids)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, type, pid, title, api, method, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $result = $this->select($selectSql)
            ->where('type', '2')
            ->whereIn('id', $Ids)
            ->orderBy('id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($result as $v) {
            if (!empty($v['api']) && !empty($v['method'])) {
                $res[] = $v['api'] . ':' . $v['method'];
            }
        }

        return $res;
    }

    public function getApiAllRecords(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, type, pid, title, api, method, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql)->orderBy('id', 'ASC');
        $data    = $builder->findAll();

        return $data;
    }
}
