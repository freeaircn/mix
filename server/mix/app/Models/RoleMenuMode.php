<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-02 21:46:46
 */

namespace App\Models;

use CodeIgniter\Model;

class RoleMenuMode extends Model
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

    public function getMenuByRole($id = null)
    {
        if (empty($id)) {
            return false;
        }

        $menu = $this->select('menu_id')
            ->where('role_id', $id)
            ->orderBy('menu_id', 'ASC')
            ->findAll();

        return $menu;
    }

    public function saveRoleMenu($role_id = null, $menus = [])
    {
        if (empty($role_id)) {
            return true;
        }

        $this->where('role_id', $role_id)->delete();

        $num = count($menus);
        $cnt = 0;
        foreach ($menus as $v) {
            $data = [
                'role_id' => $role_id,
                'menu_id' => $v,
            ];
            $this->insert($data);
            $cnt++;
        }

        return ($cnt == $num) ? true : false;
    }

}
