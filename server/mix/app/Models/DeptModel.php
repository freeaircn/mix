<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-15 09:34:07
 */

namespace App\Models;

use CodeIgniter\Model;

class DeptModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_dept';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'pid', 'status', 'description'];

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
            $selectSQL = 'id, pid, name, status, description, updated_at';
        } else {
            foreach ($columnName as $name) {
                $selectSQL = $selectSQL . $name . ', ';
            }
        }

        $builder = $this->select($selectSQL);
        if (isset($queryParam['status']) && $queryParam['status'] === 'enabled') {
            $builder->where('status', '1');
        }
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }
}
