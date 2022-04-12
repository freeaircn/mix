<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 11:25:47
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleWorkflowModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_workflow';
    protected $allowedFields = ['role_id', 'wf_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function getByRoleId(string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return [];
        }

        $res = $this->select('wf_id')
            ->where('role_id', $role_id)
            ->orderBy('wf_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function getByRoleIdsForAuth(array $roleIds = null)
    {
        if (!is_array($roleIds) || empty($roleIds)) {
            return [];
        }

        $db = $this->select('wf_id')
            ->whereIn('role_id', $roleIds)
            ->orderBy('wf_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $value) {
            $res[] = $value['wf_id'];
        }

        // 多个角色，允许有相同的id，去除重复
        return array_unique($res);
    }

    public function saveRoleWorkflow(string $role_id = null, array $wf = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->transStart();
        $this->where('role_id', $role_id)->delete();
        foreach ($wf as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id' => $role_id,
                    'wf_id'   => $v,
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
