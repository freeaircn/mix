<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-29 09:02:21
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class DtsMetaModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'meta_id';
    protected $allowedFields = ['dts_id', 'meta_key', 'meta_value'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    public function __construct()
    {
        $config        = config('MyGlobalConfig');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'dtsmeta';
        parent::__construct();
    }

    public function getLastOneByCreateDate($columnName = [], $query = [])
    {
        if (empty($columnName) || empty($query)) {
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $whereSql = "Date(created_at) = " . "'" . $query['created_at'] . "'";
        $builder->where($whereSql);
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(1);

        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function insertDB(array $draft)
    {
        if (empty($draft)) {
            return false;
        }

        // 单号
        // $time       = time();
        // $columnName = ['dts_id'];
        // $query      = ['created_at' => date('Y-m-d', $time)];

        $this->transStart();
        // $db = $this->getLastOneByCreateDate($columnName, $query);
        // if (empty($db)) {
        //     $draft['dts_id'] = date('ymd', $time) . substr('00000001', -$ticketIdTailLength);
        // } else {
        //     $id              = (int) $db['dts_id'] + 1;
        //     $draft['dts_id'] = date('ymd', $time) . substr((string) $id, -$ticketIdTailLength);
        // }
        $result = $this->insert($draft);

        $this->transComplete();
        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getByLimitOffset($columnName = [], $query = [])
    {
        if (empty($columnName) || empty($query)) {
            return ['total' => 0, 'data' => []];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);
        $builder->whereIn('station_id', $query['station_id']);
        if (isset($query['type'])) {
            $builder->where('type', $query['type']);
        }
        if (isset($query['level'])) {
            $builder->where('level', $query['level']);
        }
        if (isset($query['dts_id'])) {
            $builder->where('dts_id', $query['dts_id']);
        }
        if (isset($query['creator_id'])) {
            $builder->where('creator_id', $query['creator_id']);
        }
        if (isset($query['place_at'])) {
            $builder->where('place_at', $query['place_at']);
        }
        $builder->where('status', $query['status']);

        $total = 0;
        $total = $builder->countAllResults(false);

        if ($total === 0) {
            return ['total' => 0, 'data' => []];
        } else {
            $builder->orderBy('dts_id', 'ASC');
            $db = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);

            return ['total' => $total, 'data' => $db];
        }
    }

    public function getByDtsId($columnName = [], $query = [])
    {
        if (empty($columnName) || empty($query)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where('dts_id', $query['dts_id']);

        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function updateProgress(string $progress = '', string $dts_id = '')
    {
        if (empty($dts_id)) {
            return false;
        }
        if (empty($progress)) {
            return true;
        }

        $this->transStart();

        $columnName = ['id', 'progress'];
        $db         = $this->getByDtsId($columnName, ['dts_id' => $dts_id]);
        if (empty($db)) {
            return false;
        }
        $db['progress'] = $progress . "\n" . $db['progress'];
        $this->save($db);

        $this->transComplete();
        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function updateHandler($handler = '', $where = [])
    {
        if (!is_numeric($handler) || empty($where)) {
            return false;
        }

        $data = [
            'handler' => $handler,
        ];
        $builder = $this->where('dts_id', $where['dts_id']);
        $builder->set($data);
        return $builder->update();
    }
}
