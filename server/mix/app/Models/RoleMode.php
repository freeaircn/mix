<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-02 17:41:43
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

    public function getRoles()
    {
        $rolesTbl = $this->select('id, name, alias, status, description, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        return $rolesTbl;
    }
}
