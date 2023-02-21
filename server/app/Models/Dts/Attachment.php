<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-20 10:27:16
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class Attachment extends Model
{
    protected $DBGroup;
    protected $table;
    protected $dbPrefix;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['station_id', 'dts_id', 'user_id', 'username', 'org_name', 'new_name', 'file_ext', 'size', 'path', 'info'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $config         = config('Config\\MyConfig\\MyDB');
        $this->DBGroup  = $config->dbName;
        $this->table    = $config->dbPrefix . 'dts_attachment';
        $this->dbPrefix = $config->dbPrefix;
        parent::__construct();
    }

    public function createDtsAttachmentMultiRecords(array $attachments = null)
    {
        if (empty($attachments)) {
            return true;
        }

        return $this->insertBatch($attachments);
    }

    public function createDtsAttachmentSingleRecord(array $attachment = null)
    {
        if (empty($attachment)) {
            return true;
        }

        return $this->insert($attachment);
    }

    public function getDtsAttachmentRecordsByDtsId(array $fields = null, string $dts_id = null)
    {
        if (empty($dts_id)) {
            return [];
        }

        if (!is_numeric($dts_id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, dts_id, user_id, username, org_name, new_name, file_ext, size, path, info, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('dts_id', $dts_id);
        $db = $builder->findAll();

        return $db;
    }

    public function delDtsAttachmentRecordsByDtsId(string $dts_id = null)
    {
        if (empty($dts_id)) {
            return true;
        }

        if (!is_numeric($dts_id)) {
            return true;
        }

        $result = $this->where('dts_id', $dts_id)->delete();

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getDtsAttachmentRecordById(array $fields = null, string $id = null)
    {
        if (empty($id)) {
            return [];
        }

        if (!is_numeric($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, dts_id, user_id, username, org_name, new_name, file_ext, size, path, info, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function delDtsAttachmentRecordById(string $id = null)
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

    public function getDtsAttachmentsByMultiConditions(array $fields = null, array $conditions = null)
    {
        if (empty($conditions)) {
            return ['total' => 0, 'data' => []];
        }

        // $selectSql = '';
        // if (empty($fields)) {
        //     $selectSql = 'id, station_id, dts_id, username, org_name, created_at';
        // } else {
        //     foreach ($fields as $name) {
        //         $selectSql = $selectSql . $name . ', ';
        //     }
        // }

        $builder_ = $this->whereIn('station_id', $conditions['station_id']);

        if (isset($conditions['org_name'])) {
            $builder_->like('org_name', $conditions['org_name']);
        }

        $total = 0;
        $total = $builder_->countAllResults();

        if ($total === 0) {
            return ['total' => 0, 'data' => []];
        }

        //
        $selectSql = '';
        if (empty($fields)) {
            $fields = ['id', 'station_id', 'dts_id', 'username', 'org_name', 'created_at'];
        }
        foreach ($fields as $name) {
            $selectSql = $selectSql . $this->table . '.' . $name . ', ';
        }

        $dts  = $this->dbPrefix . 'dts';
        $dept = $this->dbPrefix . 'dept';

        $selectSql = $selectSql . sprintf("%s.title, ", $dts);
        $selectSql = $selectSql . sprintf("%s.name AS station ", $dept);

        $builder = $this->select($selectSql, false);
        $builder->from([$dts, $dept]);
        $builder->whereIn($this->table . '.station_id', $conditions['station_id']);

        if (isset($conditions['org_name'])) {
            $builder->like('org_name', $conditions['org_name']);
        }

        $whereSql = sprintf("%s.dts_id = %s.dts_id", $this->table, $dts);
        $builder->where($whereSql);
        $whereSql = sprintf("%s.station_id = %s.id", $this->table, $dept);
        $builder->where($whereSql);

        $builder->orderBy('dts_id', 'ASC');
        $result = $builder->findAll($conditions['limit'], ($conditions['offset'] - 1) * $conditions['limit']);

        return ['total' => $total, 'data' => $result];
    }
}
