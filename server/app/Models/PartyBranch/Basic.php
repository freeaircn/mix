<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-05 23:13:21
 */

namespace App\Models\PartyBranch;

use CodeIgniter\Model;

class Basic extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'category_id', 'serial_id', 'doc_num', 'title', 'keywords', 'info', 'status', 'secret_level', 'retention_period', 'store_in', 'user_id', 'username', 'deleted', 'created_at', 'updated_at'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'doc_store';
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
            $selectSql = 'id, station_id, category_id, doc_num, title, keywords, info, status, secret_level, retention_period, store_in, user_id, username, created_at, updated_at';
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
        if (isset($conditions['title'])) {
            $builder->like('title', $conditions['title']);
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

    // 2023-2-22
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

    // 2023-2-22
    public function insertOneRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
        }

        return $this->insert($data);
    }

    // 2023-2-22
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

    // 2023-2-22
    public function updateRecordById(array $data = null, string $id = null)
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }

        return $this->where('id', $id)->set($data)->update();
    }

    // 2023-2-23
    public function delRecordById(string $id = null)
    {
        if (empty($id)) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }

        $result = $this->where('id', $id)->delete();

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
    // -- 2023-2-21
}
