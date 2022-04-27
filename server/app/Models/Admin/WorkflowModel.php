<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-27 23:36:29
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class WorkflowModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_workflow';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['type', 'pid', 'name', 'workflow', 'method'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getByIds(array $Ids = null)
    {
        if (!is_array($Ids) || empty($Ids)) {
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

    public function getWorkflow(array $columnName = [])
    {
        if (empty($columnName)) {
            return [];
        }

        $selectSQL = '';
        foreach ($columnName as $name) {
            $selectSQL = $selectSQL . $name . ', ';
        }

        $builder = $this->select($selectSQL)->orderBy('id', 'ASC');
        $data    = $builder->findAll();

        return $data;
    }
}
