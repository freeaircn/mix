<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:43:33
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
        $config        = config('Config\\MyConfig\\MyDB');
        $this->DBGroup = $config->dbName;
        $this->table   = $config->dbPrefix . 'dts_attachment';
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
}
