<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 21:03:29
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleDeptModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_dept';
    protected $allowedFields = ['role_id', 'dept_id', 'data_writable', 'is_default'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function getByRoleId(string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $res = $this->select('dept_id')
            ->where('role_id', $role_id)
            ->orderBy('dept_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getByRoleId2(string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $res = $this->select('role_id, dept_id, data_writable, is_default')
            ->where('role_id', $role_id)
            ->orderBy('dept_id', 'ASC')
            ->findAll();

        return $res;
    }

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

    public function saveRoleDept($role_id = null, $dept = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->transStart();
        $this->where('role_id', $role_id)->delete();
        foreach ($dept as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id' => $role_id,
                    'dept_id' => $v,
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

    public function setRoleDept(string $role_id = null, string $dept_id = null, array $data = [])
    {
        if (!is_numeric($role_id) || !is_numeric($role_id) || empty($data)) {
            return false;
        }

        $this->transStart();
        $this->where('role_id', $role_id)->where('dept_id', $dept_id)->set($data)->update();
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

}
