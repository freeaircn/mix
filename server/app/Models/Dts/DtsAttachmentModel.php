<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-25 19:27:10
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

    public function insertDB(array $attachment)
    {
        if (empty($attachment)) {
            return true;
        }

        $this->transStart();
        foreach ($attachment as $f) {
            $this->insert($f);
        }

        $this->transComplete();
        if ($this->transStatus() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getByDtsId(array $columnName = [], array $query = [])
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
}
