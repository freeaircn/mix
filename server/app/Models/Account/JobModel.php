<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-11-01 20:33:52
 */

namespace App\Models\Account;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_job';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'status', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getJob($columnName = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, name, status, description, updated_at';
        } else {
            foreach ($columnName as $key) {
                $selectSQL = $selectSQL . $key . ', ';
            }
        }

        $res = $this->select($selectSQL)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $res;
    }
}
