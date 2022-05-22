<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:40:58
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleMenuModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $allowedFields = ['role_id', 'menu_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'role_menu';
        parent::__construct();
    }

    public function getMenuIdByRoleId(string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $res = $this->select('menu_id')
            ->where('role_id', $role_id)
            ->orderBy('menu_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getMenuIdByRoleIds(array $roleIds = null)
    {
        if (empty($roleIds)) {
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

    public function saveRoleMenuRecordsByRoleId(array $menus = null, string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $data = [];
        foreach ($menus as $v) {
            if (is_numeric($v)) {
                $data[] = [
                    'role_id' => $role_id,
                    'menu_id' => $v,
                ];
            }
        }

        $this->transStart();
        $this->where('role_id', $role_id)->delete();
        if (!empty($data)) {
            $this->insertBatch($data);
        }
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }
}
