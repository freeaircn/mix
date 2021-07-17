<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-15 10:22:37
 */

namespace App\Models;

use CodeIgniter\Model;

class RoleMode extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'alias', 'status', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getRole($columnName = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, name, alias, status, description, updated_at';
        } else {
            foreach ($columnName as $key) {
                $selectSQL = $selectSQL . $key . ', ';
            }
        }

        $res = $this->select($selectSQL)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $res;
    }
}
