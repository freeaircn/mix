<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-22 20:48:07
 */

namespace App\Models\Drawing;

use CodeIgniter\Model;

class Basic extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'category_id', 'serial_id', 'dwg_num', 'dwg_name', 'file_org_name', 'file_new_name', 'file_ext', 'size', 'path', 'keywords', 'info', 'user_id', 'username', 'deleted', 'created_at', 'updated_at'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'drawing';
        parent::__construct();
    }

    // 2023-2-21
    public function getRecordsByMultiConditions(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
            return ['total' => 0, 'data' => []];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, category_id, serial_id, dwg_num, dwg_name, keywords, file_org_name, info, user_id, username, created_at, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->whereIn('station_id', $conditions['station_id']);
        if (isset($conditions['category_id'])) {
            $builder->where('category_id', $conditions['category_id']);
        }
        // 注意
        if (isset($conditions['deleted'])) {
            $builder->where('deleted', $conditions['deleted']);
        }
        //
        if (isset($conditions['dwg_name'])) {
            $builder->like('dwg_name', $conditions['dwg_name']);
        }
        if (isset($conditions['dwg_num'])) {
            $builder->like('dwg_num', $conditions['dwg_num']);
        }
        if (isset($conditions['keywords'])) {
            $builder->like('keywords', $conditions['keywords']);
        }

        $total = 0;
        $total = $builder->countAllResults(false);

        if ($total === 0) {
            return ['total' => 0, 'data' => []];
        } else {
            $builder->orderBy('id', 'ASC');
            $result = $builder->findAll($conditions['limit'], ($conditions['offset'] - 1) * $conditions['limit']);

            return ['total' => $total, 'data' => $result];
        }
    }

    public function getLastRecordByDocNum(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
            return '0';
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'serial_id';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->like('dwg_num', $conditions['dwg_num']);
        $builder->orderBy('serial_id', 'DESC');

        $result = $builder->findAll(1);

        if (empty($result)) {
            return '0';
        } else {
            return $result[0]['serial_id'];
        }
    }

    public function insertOneRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
        }

        return $this->insert($data);
    }

    public function getRecordById(array $fields = null, $id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, station_id, category_id, serial_id, dwg_num, dwg_name, keywords, file_org_name, info, user_id, username, created_at, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $result = $builder->findAll();
        if (empty($result)) {
            return [];
        } else {
            return $result[0];
        }
    }
    // -- 2023-2-21

    public function getDtsRecordsByUpdateDate(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
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
        $builder->where('station_id', $conditions['station_id']);
        $builder->where('status', $conditions['status']);

        $builder->orderBy('updated_at', 'DESC');
        $result = $builder->findAll($conditions['limit'], ($conditions['offset'] - 1) * $conditions['limit']);

        return $result;
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
        $result = $builder->findAll();

        return isset($result[0]) ? $result[0] : [];
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
        // $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->groupBy("DATE_FORMAT(created_at, '%Y-%m')");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationYearResolveGroupByResolveMonth(string $station_id = null, string $year = null)
    {

        if (empty($station_id) || empty($year)) {
            return [];
        }

        $selectSql = "DATE_FORMAT(resolved_at, '%Y-%m') as date, COUNT(id) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("DATE_FORMAT(resolved_at, '%Y') = " . $year);
        $builder->whereIn('place_at', ['resolve', 'close']);
        $builder->groupBy("DATE_FORMAT(resolved_at, '%Y-%m')");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationAndCreatedExceedDaysGroupByPlace(string $station_id = null, int $days = null)
    {
        if (empty($station_id) || empty($days)) {
            return [];
        }

        $selectSql = "place_at, COUNT(place_at) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->whereIn('place_at', ['suspend', 'working']);
        $builder->where('DATE_SUB(CURDATE(), INTERVAL ' . $days . ' DAY) >= DATE(created_at)');
        $builder->groupBy('place_at');

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

    public function getByStationGroupByCause(string $station_id = null)
    {

        if (empty($station_id)) {
            return [];
        }

        $selectSql = "cause, COUNT(cause) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->whereIn('place_at', ['resolve', 'close']);
        $builder->groupBy("cause");

        $result = $builder->findAll();

        return $result;
    }

    public function getByStationYearGroupByCause(string $station_id = null, string $year = null)
    {

        if (empty($station_id) || empty($year)) {
            return [];
        }

        $selectSql = "cause, COUNT(cause) AS value";
        $builder   = $this->select($selectSql, false);
        $builder->where('station_id', $station_id);
        $builder->where("DATE_FORMAT(updated_at, '%Y') = " . $year);
        $builder->whereIn('place_at', ['resolve', 'close']);
        $builder->groupBy("cause");

        $result = $builder->findAll();

        return $result;
    }
}
