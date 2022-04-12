<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-11 14:30:13
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_menu';
    protected $allowedFields = ['role_id', 'menu_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

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

    public function getByRoleIdsForAuth(array $roleIds = null)
    {
        if (!is_array($roleIds) || empty($roleIds)) {
            return [];
        }

        $temp = $this->select('menu_id')
            ->whereIn('role_id', $roleIds)
            ->orderBy('menu_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($temp as $value) {
            $res[] = $value['menu_id'];
        }

        // 多个角色，允许有相同的id，去除重复
        return array_unique($res);
    }

    public function saveRoleMenu($role_id = null, $menus = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->transStart();
        $this->where('role_id', $role_id)->delete();
        foreach ($menus as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id' => $role_id,
                    'menu_id' => $v,
                ];
                $this->insert($data);
                unset($data);
            }
        }
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }
}
