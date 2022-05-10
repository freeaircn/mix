<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 21:13:50
 */

namespace App\Models\Common;

use CodeIgniter\Model;

class TitleModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'status', 'description'];

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
        $this->table   = $config->dbPrefix . 'title';
        parent::__construct();
    }

    public function getTitleAllRecords(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, name, status, description, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $res = $this->select($selectSql)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $res;
    }
}
