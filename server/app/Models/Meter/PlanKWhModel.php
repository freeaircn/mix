<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:44:12
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class PlanKWhModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'year', 'month', 'planning', 'deal', 'creator'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'kwh_planning';
        parent::__construct();
    }

    public function getKwhPlanRecordsByStationYear(string $station_id = null, string $year = null)
    {
        if (empty($station_id) || empty($year)) {
            return [];
        }

        $selectSql = 'id, month, planning, deal, creator';
        $builder   = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('year', $year);
        $res = $builder->findAll();

        return $res;
    }

    public function getKwhPlanRecordsByStationYearMonth(string $station_id = null, string $year = null, string $month = null)
    {
        if (empty($station_id) || empty($year) || empty($month)) {
            return [];
        }

        $selectSql = 'id, month, planning, deal, creator';
        $builder   = $this->select($selectSql);
        $builder->where('station_id', $station_id);
        $builder->where('year', $year);
        $builder->where('month', $month);
        $res = $builder->findAll();

        return $res;
    }

    public function updateKwhPlanRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
        }

        return $this->save($data);
    }
}
