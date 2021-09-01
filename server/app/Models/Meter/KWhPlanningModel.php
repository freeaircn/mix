<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-31 23:07:23
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class KWhPlanningModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_kwh_planning';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'year', 'type', 'month', 'planning', 'deal', 'creator'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getByStationYear($query)
    {
        if (empty($query)) {
            return false;
        }

        $selectSql = 'id, month, planning, deal';
        $builder   = $this->select($selectSql);

        $builder->where($query);
        $res = $builder->findAll();

        return $res;
    }
}
