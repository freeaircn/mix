<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-10 23:37:29
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

    public function getRoles($columnKeys = [])
    {
        // $rolesTbl = $this->select('id, name, alias, status, description, updated_at')
        //     ->orderBy('updated_at', 'DESC')
        //     ->findAll();

        // return $rolesTbl;

        $selectString = '';
        if (empty($columnKeys)) {
            $selectString = 'id, name, alias, status, description, updated_at';
        } else {
            foreach ($columnKeys as $key) {
                $selectString = $selectString . $key . ', ';
            }
        }

        $res = $this->select($selectString)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $res;
    }
}
