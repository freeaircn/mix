<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-26 20:04:47
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class PoliticModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_politic';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'status', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getPolitic($columnName = [])
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
