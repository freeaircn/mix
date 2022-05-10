<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 22:47:58
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class WorkflowModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['type', 'pid', 'name', 'workflow', 'method'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'workflow';
        parent::__construct();
    }

    public function getWorkflowRecordsByIds(array $Ids = null)
    {
        if (empty($Ids)) {
            return [];
        }

        $db = $this->select('workflow, method')
            ->where('type', '2')
            ->whereIn('id', $Ids)
            ->orderBy('id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $v) {
            if (!empty($v['workflow']) && !empty($v['method'])) {
                $res[] = $v['workflow'] . '_' . $v['method'];
            }
        }

        return $res;
    }

    public function getWorkflowAllRecords(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, type, pid, name, workflow, method, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql)->orderBy('id', 'ASC');
        $data    = $builder->findAll();

        return $data;
    }
}
