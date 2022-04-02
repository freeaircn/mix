<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-02 12:17:33
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'password', 'forceChgPwd', 'avatar', 'dept_ids', 'job', 'title', 'politic', 'last_login', 'ip_address'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'user';
        parent::__construct();
    }

    public function getByIds($columnName = [], $ids = [])
    {
        if (empty($columnName) || empty($ids)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->whereIn('id', $ids);
        $db = $builder->findAll();

        // $res = [];
        // foreach ($db as $value) {
        //     $res[] = $value['username'];
        // }

        return $db;
    }

    public function getUserByStation($columnName = [], $station = '')
    {
        if (empty($columnName) || empty($station)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->like('dept_ids', $station);
        $db = $builder->findAll();

        return $db;
    }
}
