<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-08 15:58:37
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleDeptModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_dept';
    protected $allowedFields = ['role_id', 'dept_id'];

    // protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function getByRoleIdsForAuth(array $roleIds = null)
    {
        if (!is_array($roleIds) || empty($roleIds)) {
            return [];
        }

        $db = $this->select('dept_id')
            ->whereIn('role_id', $roleIds)
            ->orderBy('dept_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $value) {
            $res[] = $value['dept_id'];
        }

        // 多个角色，允许有相同的id，去除重复
        return array_unique($res);
    }

    // public function saveRoleMenu($role_id = null, $menus = [])
    // {
    //     if (!is_numeric($role_id)) {
    //         return false;
    //     }

    //     $this->where('role_id', $role_id)->delete();

    //     $num = count($menus);
    //     $cnt = 0;
    //     foreach ($menus as $v) {
    //         if (is_numeric($v)) {
    //             $data = [
    //                 'role_id' => $role_id,
    //                 'menu_id' => $v,
    //             ];
    //             $this->insert($data);
    //             $cnt++;
    //         }
    //     }

    //     return ($cnt == $num) ? true : false;
    // }

}
