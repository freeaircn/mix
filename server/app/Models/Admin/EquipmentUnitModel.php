<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-08 20:07:24
 */

namespace App\Models\Admin;

use CodeIgniter\Model;

class EquipmentUnitModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_equipment_unit';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'pid', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function get($fields = [], $query = [])
    {
        if (empty($fields)) {
            return false;
        }

        $selectSql = '';
        foreach ($fields as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);
        $builder->where($query);
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }
}
