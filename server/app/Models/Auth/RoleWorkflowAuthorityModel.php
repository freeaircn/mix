<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-12 19:46:11
 */

namespace App\Models\Auth;

use CodeIgniter\Model;

class RoleWorkflowAuthorityModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_role_workflow_authority';
    protected $allowedFields = ['role_id', 'workflow_authority_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function getByRole($role_id = null)
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $res = $this->select('workflow_authority_id')
            ->where('role_id', $role_id)
            ->orderBy('workflow_authority_id', 'ASC')
            ->findAll();

        return $res;
    }

    public function saveByRole($role_id = null, $workflow_handler_ids = [])
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $this->where('role_id', $role_id)->delete();

        $num = count($workflow_handler_ids);
        $cnt = 0;
        foreach ($workflow_handler_ids as $v) {
            if (is_numeric($v)) {
                $data = [
                    'role_id'               => $role_id,
                    'workflow_authority_id' => $v,
                ];
                $this->insert($data);
                $cnt++;
            }
        }

        return ($cnt == $num) ? true : false;
    }

    public function getByRoles(array $roleIds = [])
    {
        if (empty($roleIds)) {
            return [];
        }

        $temp = $this->select('workflow_authority_id')
            ->whereIn('role_id', $roleIds)
            ->orderBy('workflow_authority_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($temp as $value) {
            $res[] = $value['workflow_authority_id'];
        }

        // 去除重复id
        return array_unique($res);
    }

}
