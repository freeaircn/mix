<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 22:15:07
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleDeptModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $allowedFields = ['role_id', 'dept_id', 'data_writable', 'is_default'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'role_dept';
        parent::__construct();
    }

    public function getDeptIdByRoleId(array $fields = null, string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'role_id, dept_id, data_writable, is_default';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $res = $this->select($selectSql)
            ->where('role_id', $role_id)
            ->orderBy('dept_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getDeptRecordsByRoleIds(array $roleIds = null)
    {
        if (empty($roleIds)) {
            return [];
        }

        $db = $this->select('dept_id, role_id, dept_id, data_writable, is_default')
            ->whereIn('role_id', $roleIds)
            ->orderBy('dept_id', 'ASC')
            ->findAll();

        return $db;
        // $res = [];
        // foreach ($db as $value) {
        //     $res[] = $value['dept_id'];
        // }

        // // 多个角色，允许有相同的id，去除重复
        // return array_unique($res);
    }

    public function updateRoleDept(string $role_id = null, array $delete = [], array $add = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $data = [];
        foreach ($add as $v) {
            if (is_numeric($v)) {
                $data[] = [
                    'role_id' => $role_id,
                    'dept_id' => $v,
                ];
            }
        }

        $this->transStart();
        foreach ($delete as $d) {
            $this->where('role_id', $role_id)->where('dept_id', $d)->delete();
        }
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

    public function setRoleDept(string $role_id = null, string $dept_id = null, array $data = [])
    {
        if (!is_numeric($role_id) || !is_numeric($dept_id) || empty($data)) {
            return false;
        }

        return $this->where('role_id', $role_id)->where('dept_id', $dept_id)->set($data)->update();
    }

    // public function saveRoleDept($role_id = null, $dept = [])
    // {
    //     if (!is_numeric($role_id)) {
    //         return false;
    //     }

    //     $this->transStart();
    //     $this->where('role_id', $role_id)->delete();
    //     foreach ($dept as $v) {
    //         if (is_numeric($v)) {
    //             $data = [
    //                 'role_id' => $role_id,
    //                 'dept_id' => $v,
    //             ];
    //             $this->insert($data);
    //             unset($data);
    //         }
    //     }
    //     $this->transComplete();

    //     if ($this->transStatus() === false) {
    //         return false;
    //     } else {
    //         return true;
    //     }
    // }

}
