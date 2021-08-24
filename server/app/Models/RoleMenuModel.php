<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-16 21:03:38
 */

namespace App\Models;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table = 'app_role_menu';
    // protected $primaryKey    = 'id';
    protected $allowedFields = ['role_id', 'menu_id'];

    // protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function getMenuByRole($role_id = null)
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $res = $this->select('menu_id')
            ->where('role_id', $role_id)
            ->orderBy('menu_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getMenuIdForAuthority(array $roleId = null)
    {
        if (!is_array($roleId) || empty($roleId)) {
            return [];
        }

        $temp = $this->select('menu_id')
            ->whereIn('role_id', $roleId)
            ->orderBy('menu_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($temp as $value) {
            $res[] = $value['menu_id'];
        }

        // 多个角色，允许有相同的menu id，去除重复
        return array_unique($res);
    }

    public function saveRoleMenu($role_id = null, $menus = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->where('role_id', $role_id)->delete();

        $num = count($menus);
        $cnt = 0;
        foreach ($menus as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id' => $role_id,
                    'menu_id' => $v,
                ];
                $this->insert($data);
                $cnt++;
            }
        }

        return ($cnt == $num) ? true : false;
    }

}
