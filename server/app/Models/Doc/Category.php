<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-21 23:50:51
 */

namespace App\Models\Doc;

use CodeIgniter\Model;

class Category extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'alias', 'pid', 'code'];

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
        $this->table   = $config->dbPrefix . 'doc_category';
        parent::__construct();
    }

    /**
     * 输入pid，输出子节点，默认不包括指定pid的父节点
     * @param int $pid
     * @return array
     */
    public function getChildrenByPid(array $fields = null, $pid = 0, $include_parent = false)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, code';
        } else {
            //! 注意
            if (in_array('id', $fields) === false) {
                array_push($fields, 'id');
            }
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $selectSql = trim($selectSql, ', ');

        $result      = [];
        $condition[] = (string) $pid;
        do {
            $builder = $this->select($selectSql);
            $builder->whereIn('pid', $condition);
            $db = $builder->findAll();
            unset($condition);
            foreach ($db as $k => $v) {
                $result[]    = $v;
                $condition[] = (string) $v['id'];
            }
        } while (!empty($condition));

        if ($include_parent) {
            $builder2 = $this->select($selectSql);
            $builder2->where('id', $pid);
            $db2 = $builder->findAll();
            if (!empty($db2)) {
                array_unshift($result, $db2[0]);
            }
        }

        return $result;
    }

    /**
     * 输入where条件
     * @param array $fields
     * @param array $wheres
     * @return array
     */
    public function getByWheres(array $fields = null, array $wheres = null)
    {
        if (empty($wheres)) {
            return false;
        }
        //
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, code';
        } else {
            foreach ($fields as $name) {
                $selectSql = $selectSql . $name . ', ';
            }
        }
        $selectSql = trim($selectSql, ', ');
        $builder   = $this->select($selectSql);
        foreach ($wheres as $key => $value) {
            $builder->where($key, $value);
        }
        $res = $builder->findAll();

        return $res;
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
        $selectSql = trim($selectSql, ', ');

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
        $selectSql = trim($selectSql, ', ');

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
