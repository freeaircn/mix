<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-24 19:52:44
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class DtsModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'dts_id', 'status', 'type', 'title', 'level', 'device', 'description', 'progress', 'creator_id', 'reviewer_id', 'place_at', 'score', 'score_desc', 'scored_by', 'cause', 'cause_analysis'];

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
        $this->table   = $config->dbPrefix . 'dts';
        parent::__construct();
    }

    public function createSingleDtsRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
        }

        return $this->insert($data);
    }

    public function getDtsRecordsByMultiConditions(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
            return ['total' => 0, 'data' => []];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, dts_id, status, type, title, level, device, description, progress, creator_id, reviewer_id, place_at, score, score_desc, scored_by, cause, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->whereIn('station_id', $conditions['station_id']);
        if (isset($conditions['type'])) {
            $builder->where('type', $conditions['type']);
        }
        if (isset($conditions['level'])) {
            $builder->where('level', $conditions['level']);
        }
        if (isset($conditions['dts_id'])) {
            $builder->where('dts_id', $conditions['dts_id']);
        }
        if (isset($conditions['creator_id'])) {
            $builder->where('creator_id', $conditions['creator_id']);
        }
        if (isset($conditions['place_at'])) {
            $builder->where('place_at', $conditions['place_at']);
        }
        $builder->where('status', $conditions['status']);

        $total = 0;
        $total = $builder->countAllResults(false);

        if ($total === 0) {
            return ['total' => 0, 'data' => []];
        } else {
            $builder->orderBy('dts_id', 'ASC');
            $db = $builder->findAll($conditions['limit'], ($conditions['offset'] - 1) * $conditions['limit']);

            return ['total' => $total, 'data' => $db];
        }
    }

    public function getDtsRecordByDtsId(array $fields = null, string $dts_id = null)
    {
        if (empty($dts_id)) {
            return [];
        }

        if (!is_numeric($dts_id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, dts_id, status, type, title, level, device, description, progress, creator_id, reviewer_id, place_at, score, score_desc, scored_by, cause, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->where('dts_id', $dts_id);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function updateDtsRecordById(array $data = null, string $id = null)
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }

        return $this->where('id', $id)->set($data)->update();
    }

    public function delDtsRecordByDtsId(string $dts_id = null)
    {
        if (empty($dts_id)) {
            return false;
        }

        if (!is_numeric($dts_id)) {
            return false;
        }

        $result = $this->where('dts_id', $dts_id)->delete();

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getByStationAndGroupByType(string $station_id = null)
    {

        if (empty($station_id)) {
            return [];
        }

        $selectSql = "type, COUNT(type) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->groupBy('type');

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationDeviceAndGroupByPlace(string $station_id = null, string $device_id = null)
    {

        if (empty($station_id) || empty($device_id)) {
            return [];
        }

        $selectSql = "place_at, COUNT(place_at) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->like('device', $device_id);
        $builder->groupBy('place_at');

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationYearGroupByCreateMonth(string $station_id = null, string $year = null)
    {

        if (empty($station_id) || empty($year)) {
            return [];
        }

        $selectSql = "DATE_FORMAT(created_at, '%Y-%m') as date, COUNT(id) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("DATE_FORMAT(created_at, '%Y') = " . $year);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->groupBy("DATE_FORMAT(created_at, '%Y-%m')");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationYearResolveGroupByUpdateMonth(string $station_id = null, string $year = null)
    {

        if (empty($station_id) || empty($year)) {
            return [];
        }

        $selectSql = "DATE_FORMAT(updated_at, '%Y-%m') as date, COUNT(id) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("DATE_FORMAT(updated_at, '%Y') = " . $year);
        $builder->whereIn('place_at', ['resolve', 'close']);
        $builder->groupBy("DATE_FORMAT(updated_at, '%Y-%m')");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationTypeGroupByLevel(string $station_id = null, string $type = null)
    {

        if (empty($station_id) || empty($type)) {
            return [];
        }

        $selectSql = "level, COUNT(level) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("type", $type);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->groupBy("level");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationTypeGroupByWfPlace(string $station_id = null, string $type = null)
    {

        if (empty($station_id) || empty($type)) {
            return [];
        }

        $selectSql = "place_at, COUNT(place_at) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("type", $type);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->groupBy("place_at");

        $result = $builder->findAll();

        return $result;
    }
}
