<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-13 15:40:17
 */

namespace App\Models\Generator;

use CodeIgniter\Model;

class GenEventLogModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_generator_event_log';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'generator_id', 'event', 'event_at', 'run_time', 'creator', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // V2
    public function getLastLogByStationGen($station_id = 0, $generator_id = 0, $limit = 1)
    {
        if (!is_numeric($station_id) || !is_numeric($generator_id)) {
            return [];
        }

        $selectSql = 'id, station_id, generator_id, event, event_at, run_time, creator, description';
        $builder   = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $generator_id);

        $res = $builder->orderBy('event_at', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    // V2
    public function getLogsForShowList($columnName = [], $query = [])
    {
        $selectSql = '';
        if (empty($columnName)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($columnName as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        if ($query['generator_id'] == '0') {
            $whereSql = "station_id = " . $query['station_id']
                . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        } else {
            $whereSql = "station_id = " . $query['station_id']
                . " AND generator_id = " . $query['generator_id']
                . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        }
        $builder->where($whereSql);

        $total = $builder->countAllResults(false);

        $builder->orderBy('event_at', 'DESC');
        $result = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);

        return ['total' => $total, 'result' => $result];
    }

    // V2
    public function getByStationDateGen($columnName = [], $query = [])
    {
        if (empty($columnName)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $query['station_id']);
        $builder->where('generator_id', $query['generator_id']);
        $builder->where('Year(event_at)', $query['year']);
        $builder->orderBy('event_at', 'ASC');

        $result = $builder->findAll();

        return $result;
    }

    // V
    public function getStatisticByYearAndStation($query = [])
    {
        $res = [];
        for ($i = 1; $i < 4; $i++) {
            // 统计开机次数
            $builder = $this->selectCount('event', 'run_num');
            $builder->where('Year(event_at)', $query['year']);
            $builder->where('station_id', $query['station_id']);
            $builder->where('generator_id', $i);
            $builder->where('event', 2); // 开机：2
            $arr1 = $builder->findAll();

            // 统计运行时长
            $builder = $this->selectSum('run_time', 'run_total_time');
            $builder->where('Year(event_at)', $query['year']);
            $builder->where('station_id', $query['station_id']);
            $builder->where('generator_id', $i);
            $arr2 = $builder->findAll();

            // 查询最后一条事件的事件
            $builder = $this->select('event, event_at, run_time');
            $builder->where('Year(event_at)', $query['year']);
            $builder->where('station_id', $query['station_id']);
            $builder->where('generator_id', $i);
            $arr3 = $builder->orderBy('event_at', 'DESC')
                ->findAll(1);

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

    public function getEventLog($columnName = [], $query = [])
    {
        $selectSql = '';
        if (empty($columnName)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($columnName as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        if ($query['generator_id'] == '9') {
            $whereSql = "station_id = " . $query['station_id'] . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        } else {
            $whereSql = "station_id = " . $query['station_id'] . " AND generator_id = " . $query['generator_id'] . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        }

        $builder->where($whereSql);

        $total = $builder->countAllResults(false);

        if (isset($query['limit']) && isset($query['offset'])) {
            $builder->orderBy('event_at', 'DESC');
            $result = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);
        } else {
            $builder->orderBy('event_at', 'ASC');
            $result = $builder->findAll();
        }

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

    public function delEventLogById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }
}
