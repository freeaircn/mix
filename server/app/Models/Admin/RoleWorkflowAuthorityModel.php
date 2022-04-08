<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-08 13:05:28
 */

namespace App\Models\Admin;

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
}
