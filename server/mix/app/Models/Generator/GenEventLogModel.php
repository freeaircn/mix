<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-30 23:28:56
 */

namespace App\Models\Generator;

use CodeIgniter\Model;

class GenEventLogModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_generator_event_log';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'generator_id', 'event', 'timestamp', 'run_time', 'mnt_time', 'creator', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getEventLog($columnName = [], $queryParam = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, station_id, generator_id, event, timestamp, creator, description';
        } else {
            foreach ($columnName as $key) {
                $selectSQL = $selectSQL . $key . ', ';
            }
        }
        $builder = $this->select($selectSQL);

        $builder->where('station_id', $queryParam['station_id']);
        $builder->where('timestamp >', $queryParam['startTimestamp']);
        $builder->where('timestamp <', $queryParam['endTimestamp']);

        $total = $builder->countAllResults(false);

        $builder->orderBy('timestamp', 'DESC');

        $result = $builder->findAll($queryParam['limit'], ($queryParam['offset'] - 1) * $queryParam['limit']);

        return ['total' => $total, 'result' => $result];
    }

    public function newEventLog(array $event)
    {
        if (empty($event)) {
            return false;
        }

        $result = $this->insert($event);
        return is_numeric($result) ? $result : false;
    }

    public function saveEventLog(array $event)
    {
        if (empty($event)) {
            return false;
        }

        return $this->save($event);
    }

    public function getLastEventLogByGen($columnName = [], $queryParam = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, station_id, generator_id, event, timestamp, run_time, mnt_time, creator, description';
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

        $res = $builder->orderBy('timestamp', 'DESC')
            ->limit(1)
            ->findAll();

        return isset($res[0]) ? $res[0] : [];
    }

    public function delEventLogById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }
}
