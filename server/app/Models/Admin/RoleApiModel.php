<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-11 14:29:43
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleApiModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_api';
    protected $allowedFields = ['role_id', 'api_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function getByRoleId(string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $res = $this->select('api_id')
            ->where('role_id', $role_id)
            ->orderBy('api_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getByRoleIdsForAuth(array $roleIds = null)
    {
        if (!is_array($roleIds) || empty($roleIds)) {
            return [];
        }

        $db = $this->select('api_id')
            ->whereIn('role_id', $roleIds)
            ->orderBy('api_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $value) {
            $res[] = $value['api_id'];
        }

        // 多个角色，允许有相同的id，去除重复
        return array_unique($res);
    }

    public function saveRoleApi($role_id = null, $api = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->transStart();
        $this->where('role_id', $role_id)->delete();
        foreach ($api as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id' => $role_id,
                    'api_id' => $v,
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