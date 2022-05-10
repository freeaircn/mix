<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 22:33:54
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class RoleWorkflowModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $allowedFields = ['role_id', 'wf_id'];

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'role_workflow';
        parent::__construct();
    }

    public function getWfIdByRoleId(string $role_id = null)
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

    public function getWfIdByRoleIds(array $roleIds = null)
    {
        if (empty($roleIds)) {
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

    public function saveRoleWorkflowRecordsByRoleId(array $wf = null, string $role_id = null)
    {
        if (!is_numeric($role_id)) {
            return false;
        }

        $data = [];
        foreach ($wf as $v) {
            if (is_numeric($v)) {
                $data[] = [
                    'role_id' => $role_id,
                    'wf_id'   => $v,
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
