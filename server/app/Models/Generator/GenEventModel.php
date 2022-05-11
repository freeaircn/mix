<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-11 14:12:51
 */

namespace App\Models\Generator;

use CodeIgniter\Model;

class GenEventModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'generator_id', 'event', 'cause', 'event_at', 'creator', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'generator_event_log';
        parent::__construct();
    }

    public function createGenEventSingleRecord(array $event = null)
    {
        if (empty($event)) {
            return false;
        }

        return $this->insert($event);
    }

    public function getGenEventRecordById(array $fields = null, string $id = null)
    {
        if (empty($id)) {
            return [];
        }

        if (!is_numeric($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $result = $builder->findAll();

        return isset($result[0]) ? $result[0] : [];
    }

    public function getGenEventRecordsByStationGenDate(array $fields = null, string $station_id = null, string $gen_id = null, string $year = null)
    {
        if (empty($station_id) || empty($gen_id) || empty($year)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $gen_id);
        $builder->where('Year(event_at)', $year);
        $builder->orderBy('event_at', 'ASC');

        $result = $builder->findAll();

        return $result;
    }

    public function getGenEventRecordsByStationGenYearStartStop(array $fields = null, string $station_id = null, string $gen_id = null, string $year = null, array $events = null)
    {
        if (empty($station_id) || empty($gen_id) || empty($year) || empty($events)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $gen_id);
        $builder->where('Year(event_at)', $year);
        $builder->whereIn('event', $events);
        $builder->orderBy('event_at', 'ASC');

        $result = $builder->findAll();

        return $result;
    }

    public function updateGenEventRecord(array $event = null)
    {
        if (empty($event)) {
            return false;
        }

        return $this->save($event);
    }

    public function delGenEventRecordById(string $id = null)
    {
        if (empty($id)) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }

        return $this->delete($id);
    }

    public function getGenEventLatestRecordByStation(array $fields = null, string $station_id = null, $limit = 1)
    {
        if (empty($station_id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->where('station_id', $station_id);

        $res = $builder->orderBy('event_at', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function getGenEventRecordsByMultiConditions(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
            return ['total' => 0, 'result' => []];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        // if ($query['generator_id'] == '0') {
        //     $whereSql = "station_id = " . $query['station_id']
        //         . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        // } else {
        //     $whereSql = "station_id = " . $query['station_id']
        //         . " AND generator_id = " . $query['generator_id']
        //         . " AND Date(event_at) BETWEEN " . "'" . $query['start'] . "'" . " AND " . "'" . $query['end'] . "'";
        // }
        $whereSql = "station_id = " . $conditions['station_id'];
        if ($conditions['generator_id'] !== '0') {
            $whereSql = $whereSql . " AND generator_id = " . $conditions['generator_id'];
        }
        if ($conditions['event'] !== '0') {
            $whereSql = $whereSql . " AND event = " . $conditions['event'];
        }
        if ($conditions['description'] !== '0') {
            $whereSql = $whereSql . " AND description != " . "'无'";
        }
        $whereSql = $whereSql . " AND Date(event_at) BETWEEN " . "'" . $conditions['start'] . "'" . " AND " . "'" . $conditions['end'] . "'";

        $builder->where($whereSql);

        $total = $builder->countAllResults(false);

        $builder->orderBy('event_at', 'DESC');
        $result = $builder->findAll($conditions['limit'], ($conditions['offset'] - 1) * $conditions['limit']);

        return ['total' => $total, 'result' => $result];
    }

    public function getGenEventLatestRecordByStationGenEvents(array $fields = null, string $station_id = null, string $gen_id = null, array $events = null, int $limit = 1)
    {
        if (empty($station_id) || empty($gen_id) || empty($events)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $gen_id);
        $builder->whereIn('event', $events);

        $res = $builder->orderBy('event_at', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function getGenEventPrevByStationGenEvents(array $fields = null, string $station_id = null, string $gen_id = null, array $events = null, string $event_at = null, int $limit = 1)
    {
        if (empty($station_id) || empty($gen_id) || empty($events) || empty($event_at)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $gen_id);
        $builder->whereIn('event', $events);
        $builder->where("unix_timestamp(event_at) < unix_timestamp('" . $event_at . "')");

        $res = $builder->orderBy('event_at', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    public function getGenEventNextByStationGenEvents(array $fields = null, string $station_id = null, string $gen_id = null, array $events = null, string $event_at = null, int $limit = 1)
    {
        if (empty($station_id) || empty($gen_id) || empty($events) || empty($event_at)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }
        $builder = $this->select($selectSql);

        $builder->where('station_id', $station_id);
        $builder->where('generator_id', $gen_id);
        $builder->whereIn('event', $events);
        $builder->where("unix_timestamp(event_at) > unix_timestamp('" . $event_at . "')");

        $res = $builder->orderBy('event_at', 'DESC')
            ->findAll($limit);

        if ($limit === 1) {
            return isset($res[0]) ? $res[0] : [];
        } else {
            return $res;
        }
    }

    // public function getLastRecordByStationGId($station_id = 0, $generator_id = 0, $limit = 1)
    // {
    //     if (!is_numeric($station_id) || !is_numeric($generator_id)) {
    //         return [];
    //     }

    //     $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
    //     $builder   = $this->select($selectSql);

    //     $builder->where('station_id', $station_id);
    //     $builder->where('generator_id', $generator_id);

    //     $res = $builder->orderBy('event_at', 'DESC')
    //         ->findAll($limit);

    //     if ($limit === 1) {
    //         return isset($res[0]) ? $res[0] : [];
    //     } else {
    //         return $res;
    //     }
    // }
}
