<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-04 19:56:34
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

    // public function getMenuIdForAuthority(array $roleId = null)
    // {
    //     if (!is_array($roleId) || empty($roleId)) {
    //         return [];
    //     }

    //     $temp = $this->select('menu_id')
    //         ->whereIn('role_id', $roleId)
    //         ->orderBy('menu_id', 'ASC')
    //         ->findAll();

    //     $res = [];
    //     foreach ($temp as $value) {
    //         $res[] = $value['menu_id'];
    //     }

    //     // 多个角色，允许有相同的menu id，去除重复
    //     return array_unique($res);
    // }

}
