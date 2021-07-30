<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-30 16:56:15
 */

namespace App\Models\Generator;

use CodeIgniter\Model;

class GenEventStatisticModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_generator_event_statistic';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'generator_id', 'year', 'status', 'run_at', 'mnt_at', 'run_num', 'mnt_num', 'run_total_time', 'mnt_total_time'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getStatisticByGen($columnName = [], $queryParam = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, station_id, generator_id, year, status, run_at, mnt_at, run_num, mnt_num, run_total_time, mnt_total_time';
        } else {
            foreach ($columnName as $key) {
                $selectSQL = $selectSQL . $key . ', ';
            }
        }
        $builder = $this->select($selectSQL);

        if (isset($queryParam['station_id'])) {
            $builder->where('station_id', $queryParam['station_id']);
        }
        if (isset($queryParam['generator_id'])) {
            $builder->where('generator_id', $queryParam['generator_id']);
        }
        if (isset($queryParam['year'])) {
            $builder->where('year', $queryParam['year']);
        }

        $res = $builder->findAll();

        return isset($res[0]) ? $res[0] : [];
    }

    public function newStatistic(array $statistic)
    {
        if (empty($statistic)) {
            return false;
        }

        $result = $this->insert($statistic);
        return is_numeric($result) ? $result : false;
    }

    public function saveStatistic(array $statistic)
    {
        if (empty($statistic)) {
            return false;
        }

        return $this->save($statistic);
    }

    public function delStatisticById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }
}
