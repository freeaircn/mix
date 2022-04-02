<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-02 10:44:07
 */

namespace App\Models\Dts;

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
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'equipment_unit';
        parent::__construct();
    }

    public function get($columnName = [], $query = [])
    {
        if (empty($columnName)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);
        $builder->where($query);
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }

    public function getByIds($columnName = [], $query = [])
    {
        if (empty($columnName) || empty($query)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);
        $builder->whereIn('id', $query['ids']);
        $builder->orderBy('id', 'ASC');

        return $builder->findAll();
    }
}
