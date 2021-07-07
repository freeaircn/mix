<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-06 22:25:54
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

    public function getDept()
    {
        $dept = $this->select('id, pid, name, status, description, updated_at')
            ->orderBy('id', 'ASC')
            ->findAll();

        // $utils = service('mixUtils');
        // $utils = Services::mixUtils();
        // $res   = $utils->arr2tree($dept);

        return $dept;
    }
}
