<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-10 23:02:55
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

    public function getDept($columnKeys = [])
    {
        $selectString = '';
        if (empty($columnKeys)) {
            $selectString = 'id, pid, name, status, description, updated_at';
        } else {
            foreach ($columnKeys as $key) {
                $selectString = $selectString . $key . ', ';
            }
        }

        $dept = $this->select($selectString)
            ->orderBy('id', 'ASC')
            ->findAll();

        // $utils = service('mixUtils');
        // $utils = Services::mixUtils();
        // $res   = $utils->arr2tree($dept);

        return $dept;
    }
}
