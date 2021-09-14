<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-14 10:25:45
 */

namespace App\Models\Generator;

use CodeIgniter\Model;

class GenEventLogModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_generator_event_log';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'generator_id', 'event', 'event_at', 'creator', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function newEventLog(array $event)
    {
        if (empty($event)) {
            return false;
        }

        $result = $this->insert($event);
        return is_numeric($result) ? $result : false;
    }

    public function getLastLogByStationGen($station_id = 0, $generator_id = 0, $limit = 1)
    {
        if (!is_numeric($station_id) || !is_numeric($generator_id)) {
            return [];
        }

        $selectSql = 'id, station_id, generator_id, event, event_at, creator, description';
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
