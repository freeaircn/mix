<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-09 10:13:28
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class ApiModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_api';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['type', 'pid', 'title', 'api', 'method'];

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

        $db = $this->select('api, method')
            ->where('type', '2')
            ->whereIn('id', $Ids)
            ->orderBy('id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($db as $v) {
            if (!empty($v['api']) && !empty($v['method'])) {
                $res[] = $v['api'] . ':' . $v['method'];
            }
        }

        return $res;
    }

    public function getApis($columnName = [])
    {
        $selectSQL = '';
        foreach ($columnName as $name) {
            $selectSQL = $selectSQL . $name . ', ';
        }

        $builder = $this->select($selectSQL)->orderBy('id', 'ASC');
        $data    = $builder->findAll();

        return $data;
    }
}
