<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 22:43:03
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $allowedFields = ['user_id', 'role_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'user_role';
        parent::__construct();
    }

    public function getRoleIdByUid(string $uid = null)
    {
        if (!is_numeric($uid)) {
            return [];
        }

        $temp = $this->select('role_id')
            ->where('user_id', $uid)
            ->orderBy('role_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($temp as $value) {
            $res[] = $value['role_id'];
        }

        return $res;
    }

    public function createUserRoleRecordsByUid(array $role = null, string $uid = null)
    {
        if (!(is_numeric($uid))) {
            return false;
        }

        $data = [];
        foreach ($role as $v) {
            if (is_numeric($v)) {
                $data[] = [
                    'user_id' => $uid,
                    'role_id' => $v,
                ];
            }
        }

        $this->transStart();
        $this->where('user_id', $uid)->delete();
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

    public function delUserRoleRecordByUid(string $uid = null)
    {
        if (!(is_numeric($uid))) {
            return true;
        }

        return $this->where('user_id', $uid)->delete();
    }

}
