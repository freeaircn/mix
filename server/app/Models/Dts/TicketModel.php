<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-03-24 20:20:19
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_dts';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'dts_id', 'type', 'title', 'level', 'device', 'progress', 'creator', 'handler', 'reviewer', 'place_at', 'task_id', 'rm_id', 'score', 'score_comment', 'cause'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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

    public function newDraft(array $draft, int $ticketIdTailLength = 3)
    {
        if (empty($draft)) {
            return false;
        }

        // 单号
        $time       = time();
        $columnName = ['dts_id'];
        $query      = ['created_at' => date('Y-m-d', $time)];

        $this->transStart();
        $db = $this->getLastOneByCreateDate($columnName, $query);
        if (empty($db)) {
            $draft['dts_id'] = date('ymd', $time) . substr('00000001', -$ticketIdTailLength);
        } else {
            $id              = (int) $db['dts_id'] + 1;
            $draft['dts_id'] = date('ymd', $time) . substr((string) $id, -$ticketIdTailLength);
        }
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
            return false;
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);
        $builder->where('station_id', $query['station_id']);

        $total = 0;
        $total = $builder->countAllResults(false);

        $builder->orderBy('dts_id', 'ASC');
        $db = $builder->findAll($query['limit'], ($query['offset'] - 1) * $query['limit']);

        return ['total' => $total, 'data' => $db];
    }

    public function getByTicketId($columnName = [], $query = [])
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

    public function updateProgress(string $progress = '', $where = [])
    {
        if (empty($progress) || empty($where)) {
            return false;
        }

        $columnName = ['id', 'progress'];
        $this->transStart();
        $db = $this->getByTicketId($columnName, $where);
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

    public function myUpdate($data = [])
    {
        if (empty($data)) {
            return false;
        }

        return $this->save($data);
    }

}
