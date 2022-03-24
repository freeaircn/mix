<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-03-24 20:46:34
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class WorkflowAuthModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_workflow_authority';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pid', 'name', 'alias', 'description', 'created_at', 'updated_at', 'deleted_at'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function get($columnName = [], $query = [])
    {
        if (empty($columnName)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->orderBy('id', 'ASC');
        $db = $builder->findAll();

        return $db;
    }

    public function getByAlias($columnName = [], $query = [])
    {
        if (empty($columnName)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where($query);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }
}
