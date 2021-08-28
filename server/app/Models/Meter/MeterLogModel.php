<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-28 22:51:24
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class MeterLogModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_meter';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'meter_id', 'log_at', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley', 'fak_delta', 'bak_delta', 'frk_delta', 'brk_delta', 'peak_delta', 'valley_delta', 'creator'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getLastLogsByStation($station_id = 0, $limit = 9)
    {
        if (!is_numeric($station_id)) {
            return [];
        }

        $selectSQL = 'id, station_id, meter_id, log_at, fak, bak, frk, brk, peak, valley, fak_delta, bak_delta, frk_delta, brk_delta, peak_delta, valley_delta, creator';
        $builder   = $this->select($selectSQL);

        $builder->where('station_id', $station_id);

        $res = $builder->orderBy('log_at', 'DESC')
            ->limit($limit)
            ->findAll();

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function batchInsert(array $meters, array $others)
    {
        if (empty($meters) || empty($others)) {
            return false;
        }

        $this->transStart();
        for ($i = 0; $i < count($meters); $i++) {
            $db_data = $others;

            $db_data['meter_id'] = $i + 1;
            foreach ($meters[$i] as $key => $value) {
                $db_data[$key] = $value;
            }
            $result = $this->insert($db_data);
            unset($db_data);
        }
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getEventLog($columnName = [], $queryParam = [])
    {
        $selectSQL = '';
        if (empty($columnName)) {
            $selectSQL = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($columnName as $key) {
                $selectSQL = $selectSQL . $key . ', ';
            }
        }
        $builder = $this->select($selectSQL);

        if ($queryParam['generator_id'] == '9') {
            $whereSql = "station_id = " . $queryParam['station_id'] . " AND Date(event_at) BETWEEN " . "'" . $queryParam['start'] . "'" . " AND " . "'" . $queryParam['end'] . "'";
        } else {
            $whereSql = "station_id = " . $queryParam['station_id'] . " AND generator_id = " . $queryParam['generator_id'] . " AND Date(event_at) BETWEEN " . "'" . $queryParam['start'] . "'" . " AND " . "'" . $queryParam['end'] . "'";
        }

        $builder->where($whereSql);

        $total = $builder->countAllResults(false);

        if (isset($queryParam['limit']) && isset($queryParam['offset'])) {
            $builder->orderBy('event_at', 'DESC');
            $result = $builder->findAll($queryParam['limit'], ($queryParam['offset'] - 1) * $queryParam['limit']);
        } else {
            $builder->orderBy('event_at', 'ASC');
            $result = $builder->findAll();
        }

        return ['total' => $total, 'result' => $result];
    }

    public function saveEventLog(array $event)
    {
        if (empty($event)) {
            return false;
        }

        return $this->save($event);
    }

    public function delEventLogById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }

    public function getStatisticByYearAndStation($queryParam = [])
    {
        $res = [];
        for ($i = 1; $i < 4; $i++) {
            // 统计开机次数
            $builder = $this->selectCount('event', 'run_num');
            $builder->where('Year(event_at)', $queryParam['year']);
            $builder->where('station_id', $queryParam['station_id']);
            $builder->where('generator_id', $i);
            $builder->where('event', 2); // 开机：2
            $arr1 = $builder->findAll();

            // 统计运行时长
            $builder = $this->selectSum('run_time', 'run_total_time');
            $builder->where('Year(event_at)', $queryParam['year']);
            $builder->where('station_id', $queryParam['station_id']);
            $builder->where('generator_id', $i);
            $arr2 = $builder->findAll();

            // 查询最后一条事件的事件
            $builder = $this->select('event, event_at, run_time');
            $builder->where('Year(event_at)', $queryParam['year']);
            $builder->where('station_id', $queryParam['station_id']);
            $builder->where('generator_id', $i);
            $arr3 = $builder->orderBy('event_at', 'DESC')
                ->limit(1)
                ->findAll();

            $latest_time = '0';
            if (isset($arr3[0])) {
                // 事件：开机，run_time：不等于0，开机运行跨年
                if ($arr3[0]['event'] == 2 && $arr3[0]['run_time'] != 0) {
                    $latest_time = substr($arr3[0]['event_at'], 0, 10) . " 23:59:59";
                } else {
                    $latest_time = $arr3[0]['event_at'];
                }
            }

            $res[] = [
                'generator_id'   => $i,
                'run_num'        => isset($arr1[0]) ? $arr1[0]['run_num'] : 0,
                'run_total_time' => isset($arr2[0]) ? $arr2[0]['run_total_time'] : 0,
                'latest_time'    => $latest_time,
            ];

        }

        return $res;
    }
}
