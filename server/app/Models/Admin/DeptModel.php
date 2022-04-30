<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-30 23:56:31
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class DeptModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_dept';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'pid', 'status', 'description', 'dataMask'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getDept($columnName = [], $queryParam = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, pid, name, status, description, dataMask, updated_at';
        } else {
            foreach ($columnName as $name) {
                $selectSQL = $selectSQL . $name . ', ';
            }
        }
        $builder = $this->select($selectSQL);

        if (isset($queryParam['status']) && $queryParam['status'] === 'enabled') {
            $builder->where('status', '1');
        }
        if (isset($queryParam['ids']) && !empty($queryParam['ids'])) {
            $builder->whereIn('id', $queryParam['ids']);
        }

        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }

    public function getById($columnName = [], $id)
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'name';
        } else {
            foreach ($columnName as $name) {
                $selectSQL = $selectSQL . $name . ', ';
            }
        }
        $builder = $this->select($selectSQL);
        $builder->where('id', $id);
        $res = $builder->findAll();

        return isset($res[0]) ? $res[0] : [];
    }

    public function getByIds(array $columnName = null, array $Ids = null)
    {
        if (empty($columnName) || empty($Ids)) {
            return [];
        }

        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'name';
        } else {
            foreach ($columnName as $name) {
                $selectSQL = $selectSQL . $name . ', ';
            }
        }
        $builder = $this->select($selectSQL);
        $builder->whereIn('id', $Ids);
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }
}
