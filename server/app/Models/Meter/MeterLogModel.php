<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-11-21 23:06:04
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class MeterLogModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_meter';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'meter_id', 'log_date', 'log_time', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley', 'creator'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function hasSameLogByStationAndDateTime($station_id, $log_date = '', $log_time = '')
    {
        if (!is_numeric($station_id) || $log_date === '' || $log_time === '') {
            return true;
        }

        $builder = $this->where('station_id', $station_id);
        $builder->where('log_date', $log_date);
        $builder->where('log_time', $log_time);

        $total = $builder->countAllResults();

        return $total > 0 ? true : false;
    }

    public function getByStationDateTime($columnName = [], $query)
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
        $builder->where('log_date', $query['log_date']);
        $builder->where('log_time', $query['log_time']);

        $result = $builder->findAll();

        return $result;
    }

    public function getById($id)
    {
        if (!is_numeric($id) || $id < 0) {
            return false;
        }

        $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator';
        $builder   = $this->select($selectSql);

        $builder->where('id', $id);
        $res = $builder->findAll();

        return $res;
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

    public function getLogsForShowList($columnName = [], $query = [])
    {
        $selectSql = '';
        if (empty($columnName)) {
            $selectSql = 'id, log_date, log_time, creator';
        } else {
            foreach ($columnName as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $whereSql = "station_id = " . $query['station_id']
            . " AND meter_id = " . "1"
            . " AND Date(log_date) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        $builder->where($whereSql);

        $total = $builder->countAllResults(false);

        $builder->orderBy('log_date', 'DESC');
        $result = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);

        return ['total' => $total, 'result' => $result];
    }

    // public function sumByStationDateTimeMeterIds($columnName = [], $query = [])
    // {
    //     if (empty($columnName)) {
    //         return false;
    //     }

    //     $selectSql = '';
    //     foreach ($columnName as $key) {
    //         $selectSql = $selectSql . 'SUM(`' . $key . '`) AS sum_' . $key . ', ';
    //     }
    //     $builder = $this->select($selectSql);

    //     $whereSql = "station_id = " . $query['station_id']
    //         . " AND log_date = " . "'" . $query['log_date'] . "'"
    //         . " AND log_time = " . "'" . $query['log_time'] . "'"
    //         . " AND meter_id BETWEEN " . $query['first_meter_id'] . " AND " . $query['last_meter_id'];
    //     $builder->where($whereSql);

    //     $result = $builder->findAll();
    //     return isset($result[0]) ? $result[0] : [];
    // }

    public function getByStationDatesTimeMeters($columnName = [], $query = [])
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
        $builder->where('log_time', $query['log_time']);
        $builder->whereIn('log_date', $query['log_date']);
        $builder->whereIn('meter_id', $query['meter_id']);

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationDatesTimeMeter($columnName = [], $query = [])
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
        $builder->where('log_time', $query['log_time']);
        $builder->where('meter_id', $query['meter_id']);
        $builder->whereIn('log_date', $query['log_date']);

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationDateRangeTimeMeters($columnName = [], $query = [])
    {
        if (empty($columnName)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $whereSql = "station_id = " . $query['station_id']
            . " AND log_time = " . "'" . $query['log_time'] . "'"
            . " AND Date(log_date) BETWEEN " . "'" . $query['start_at'] . "'" . " AND " . "'" . $query['end_at'] . "'"
            . " AND meter_id BETWEEN " . $query['first_meter_id'] . " AND " . $query['last_meter_id'];
        $builder->where($whereSql);

        $result = $builder->findAll();
        return $result;
    }

    public function getLastDateByStationTime($columnName = [], $query = [], $limit = 1)
    {
        if (empty($columnName)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where($query);

        $res = $builder->orderBy('log_date', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function getLastDateByStationTimeMeters($columnName = [], $query = [], $limit = 1)
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
        $builder->where('log_time', $query['log_time']);
        $builder->whereIn('meter_id', $query['meter_id']);

        $res = $builder->orderBy('log_date', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function deleteByStationDateTime($query)
    {
        if (empty($query)) {
            return false;
        }

        return $this->where($query)->delete();
    }
}
