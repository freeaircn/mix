<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-04 21:06:04
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_user_role';
    protected $allowedFields = ['user_id', 'role_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function getWhereInRole($role_id = [])
    {
        if (empty($role_id)) {
            return [];
        }

        $db = $this->select('user_id')
            ->whereIn('role_id', $role_id)
            ->orderBy('user_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $value) {
            $res[] = $value['user_id'];
        }

        return $res;
    }
}
