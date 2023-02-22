<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-22 21:43:02
 */

namespace App\Models\Drawing;

use CodeIgniter\Model;

class Category extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'alias', 'pid', 'status', 'description'];

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
        $this->table   = $config->dbPrefix . 'drawing_category';
        parent::__construct();
    }

    public function getAll(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, alias, status, description, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $builder = $this->select($selectSql);
        // 注意
        $builder->where('pid', '1');
        $builder->orderBy('id', 'ASC');

        $res = $builder->findAll();

        return $res;
    }

    public function getById(array $fields = null, $id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, alias, status, description, updated_at';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $res = $builder->findAll();

        if (empty($res)) {
            return [];
        } else {
            return $res[0];
        }
    }

}
