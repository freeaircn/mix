<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-11 11:02:21
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class DtsModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'dts_id', 'status', 'type', 'title', 'level', 'device', 'description', 'progress', 'creator_id', 'reviewer_id', 'place_at', 'score', 'score_desc', 'scored_by', 'cause'];

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
}
