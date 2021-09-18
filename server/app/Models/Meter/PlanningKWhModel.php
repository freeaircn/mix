<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-17 22:42:36
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class PlanningKWhModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_kwh_planning';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'year', 'month', 'planning', 'deal', 'creator'];

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

        $selectSql = 'id, month, planning, deal, creator';
        $builder   = $this->select($selectSql);

        $builder->where($query);
        $res = $builder->findAll();

        return $res;
    }

    public function getByStationYearMonth($query)
    {
        if (empty($query)) {
            return false;
        }

        $selectSql = 'id, month, planning, deal, creator';
        $builder   = $this->select($selectSql);

        $builder->where($query);
        $res = $builder->findAll();

        return $res;
    }

    public function doSave(array $data)
    {
        if (empty($data)) {
            return false;
        }

        return $this->save($data);
    }
}
