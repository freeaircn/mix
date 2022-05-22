<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:44:07
 */

namespace App\Models\Meter;

use CodeIgniter\Model;

class MeterModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'meter_id', 'log_date', 'log_time', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley', 'creator'];

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
        $this->table   = $config->dbPrefix . 'meter';
        parent::__construct();
    }

    public function hasSameRecordByStationAndDateTime(string $station_id = null, string $log_date = null, string $log_time = null)
    {
        if (!is_numeric($station_id) || empty($log_date) || empty($log_time)) {
            return true;
        }

        $builder = $this->where('station_id', $station_id);
        $builder->where('log_date', $log_date);
        $builder->where('log_time', $log_time);

        $total = $builder->countAllResults();

        return $total > 0 ? true : false;
    }

    public function getMeterRecordsByStationDateTime(array $fields = null, string $station_id, string $date = null, string $time = null)
    {
        if (!is_numeric($station_id) || empty($date) || empty($time)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('log_date', $date);
        $builder->where('log_time', $time);

        $result = $builder->findAll();

        return $result;
    }

    public function getMeterRecordById(string $id = null)
    {
        if (!is_numeric($id)) {
            return [];
        }

        $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator';
        $builder   = $this->select($selectSql);

        $builder->where('id', $id);
        $result = $builder->findAll();

        return isset($result[0]) ? $result[0] : [];
    }

    public function createMeterMultiRecords(array $meters = null, array $others = null)
    {
        if (empty($meters) || empty($others)) {
            return false;
        }

        $data = [];
        $cnt  = count($meters);
        for ($i = 0; $i < $cnt; $i++) {
            $temp             = $others;
            $temp['meter_id'] = $i + 1;
            foreach ($meters[$i] as $key => $value) {
                $temp[$key] = $value;
            }
            $data[] = $temp;
            unset($temp);
        }

        $this->transStart();
        if (!empty($data)) {
            $this->insertBatch($data);
        }
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function updateMeterMultiRecords(array $meters = null, array $others = null, array $where = null)
    {
        if (empty($meters) || empty($others) || empty($where)) {
            return false;
        }

        $this->transStart();
        $cnt = count($meters);
        for ($i = 0; $i < $cnt; $i++) {
            $db_data = $others;

            $where['meter_id'] = (string) ($i + 1);
            foreach ($meters[$i] as $key => $value) {
                $db_data[$key] = $value;
            }

            $builder = $this->where('station_id', $where['station_id']);
            $builder->where('log_date', $where['log_date']);
            $builder->where('log_time', $where['log_time']);
            $builder->where('meter_id', $where['meter_id']);
            $result = $builder->set($db_data)->update();

            unset($db_data);
        }
        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getMeterRecordsByStationDatesTimeMeters(array $fields = null, array $query = null)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $query['station_id']);
        $builder->where('log_time', $query['log_time']);
        $builder->whereIn('log_date', $query['log_date']);
        $builder->whereIn('meter_id', $query['meter_id']);

        $result = $builder->findAll();

        return $result;
    }

    public function getMeterRecordsByStationDatesTimeMeter(array $fields = null, array $query = null)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $query['station_id']);
        $builder->where('log_time', $query['log_time']);
        $builder->where('meter_id', $query['meter_id']);
        $builder->whereIn('log_date', $query['log_date']);

        $result = $builder->findAll();

        return $result;
    }

    public function getMeterRecordsByStationDateRangeTimeMeters(array $fields = null, array $query = null)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
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

    public function getMeterLatestRecordsByStationTimeMeters(array $fields = null, array $query = null, $limit = 1)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
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

    public function getMeterLatestRecordsByStation(array $fields = null, array $query = null, $limit = 1)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
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

    public function getMeterRecordsByStationDateRangeTime(array $fields = null, array $query = null, $limit = 1)
    {
        if (empty($query)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $whereSql = "station_id = " . $query['station_id']
            . " AND log_time = " . "'" . $query['log_time'] . "'"
            . " AND Date(log_date) BETWEEN " . "'" . $query['start_at'] . "'" . " AND " . "'" . $query['end_at'] . "'";
        $builder->where($whereSql);

        // $result = $builder->findAll();
        // return $result;

        $res = $builder->orderBy('log_date', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function delMeterRecordsByStationDateTime(array $query = null)
    {
        if (empty($query)) {
            return false;
        }

        return $this->where($query)->delete();
    }

    public function getMeterRecordsForListView(array $fields = null, array $query = null)
    {
        if (empty($query)) {
            return ['total' => 0, 'data' => []];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, log_date, log_time, creator';
        } else {
            foreach ($fields as $key) {
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
        $db = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);

        return ['total' => $total, 'data' => $db];
    }

    // public function getLastDateByStationTime(array $fields = null, array $query = null, int $limit = 1)
    // {
    //     if (empty($query)) {
    //         return [];
    //     }

    //     $selectSql = '';
    //     if (empty($fields)) {
    //         $selectSql = 'id, station_id, meter_id, log_date, log_time, fak, bak, frk, brk, peak, valley, creator, updated_at';
    //     } else {
    //         foreach ($fields as $name) {
    //             $selectSql = $selectSql . $name . ', ';
    //         }
    //     }
    //     $builder = $this->select($selectSql);

    //     $builder->where($query);

    //     $res = $builder->orderBy('log_date', 'DESC')
    //         ->findAll($limit);

    //     if ($limit === 1) {
    //         return isset($res[0]) ? $res[0] : [];
    //     } else {
    //         return $res;
    //     }
    // }
}
