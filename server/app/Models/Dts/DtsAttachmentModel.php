<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-27 21:47:48
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class DtsAttachmentModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['dts_id', 'user_id', 'username', 'org_name', 'new_name', 'file_ext', 'size', 'path', 'info'];

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
        $this->table   = $config->dbPrefix . 'dts_attachment';
        parent::__construct();
    }

    public function insertMultiRecords(array $attachments = null)
    {
        if (empty($attachments)) {
            return true;
        }

        $result = $this->insertBatch($attachments);
        return $result;
    }

    public function insertSingleRecord(array $attachment)
    {
        if (empty($attachment)) {
            return false;
        }

        $result = $this->insert($attachment);
        return $result;
    }

    public function getByDtsId(array $columnName = [], string $dts_id = '')
    {
        if (empty($columnName) || empty($dts_id)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where('dts_id', $dts_id);

        $db = $builder->findAll();

        return $db;
    }

    public function delByDtsId(string $dts_id)
    {
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

    public function getById(array $columnName = [], array $query = [])
    {
        if (empty($columnName) || empty($query)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $key) {
            $selectSql = $selectSql . $key . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->where('id', $query['id']);

        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function delById(string $id)
    {
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
}