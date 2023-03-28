<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-28 21:42:58
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
     * 设置where条件查找
     *
     * @author freeair
     * @DateTime 2023-03-28
     * @param array|null $fields
     * @param array|null $wheres
     * @return array
     */
    public function getByWheres(array $fields = null, array $wheres = null)
    {
        if (empty($wheres)) {
            return [];
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

    /**
     * 查找主键ID记录
     *
     * @author freeair
     * @DateTime 2023-03-28
     * @param array|null $fields
     * @param integer $id
     * @return array
     */
    public function getById(array $fields = null, $id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, alias, code, created_at, updated_at';
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

    /**
     * MYSQL 递归查找所有父节点，输出包括指定子节点
     *
     * @author freeair
     * @DateTime 2023-03-26
     * @param array|null $fields
     * @param integer $id 指定子节点id
     * @param integer $top_pid 查找到该父节点id时，停止查找
     * @return array
     */
    public function getAllParents(array $fields = null, $id = 0, $top_pid = 1)
    {
        if (empty($fields) || empty($id)) {
            return [];
        }

        $fields_sql = '';
        foreach ($fields as $name) {
            $fields_sql = $fields_sql . 't2.' . $name . ', ';
        }
        $fields_sql = trim($fields_sql, ', ');

        $sql = "SELECT " . $fields_sql . "
                FROM (
                        SELECT
                        @r AS _id,
                        (SELECT @r := pid FROM " . $this->table . " WHERE id = _id) AS pid,
                        @l := @l + 1 AS level
                        FROM
                        (SELECT @r := :CHILD_ID:, @l := 0) vars, " . $this->table . " AS h
                        WHERE @r <> :TOP_PID:
                    ) t1
                JOIN " . $this->table . " t2
                ON t1._id = t2.id
                ";

        $query = $this->query($sql, [
            'CHILD_ID' => $id,
            'TOP_PID'  => $top_pid,
        ]);

        $result = $query->getResultArray();
        return $result;
    }

    /**
     * MYSQL 递归查找所有子节点，输出不包括指定父节点
     *
     * @author freeair
     * @DateTime 2023-03-27
     * @param array|null $fields
     * @param integer $pid 指定父节点
     * @param boolean $include_self 输出是否包括指定父节点
     * @return array
     */
    public function getAllChildren(array $fields = null, $pid = 0, $include_self = false)
    {
        if (empty($fields) || empty($pid)) {
            return [];
        }

        $fields_sql_a = '';
        foreach ($fields as $name) {
            $fields_sql_a = $fields_sql_a . $name . ', ';
        }
        $fields_sql_a = trim($fields_sql_a, ', ');

        $new_fields   = array_merge(['id', 'pid'], $fields);
        $new_fields   = array_unique($new_fields);
        $fields_sql_b = '';
        $fields_sql_c = '';
        foreach ($new_fields as $name) {
            $fields_sql_b = $fields_sql_b . 't1.' . $name . ', ';
            $fields_sql_c = $fields_sql_c . $name . ', ';
        }
        $fields_sql_c = trim($fields_sql_c, ', ');

        $sql = "SELECT " . $fields_sql_a . "
                FROM (
                    SELECT " . $fields_sql_b . "
                    IF(find_in_set(pid, @pids) > 0, @pids := concat(@pids, ',', id), -1) AS isChild
                    FROM
                        (SELECT " . $fields_sql_c . " FROM " . $this->table . " ) t1,
                        (SELECT @pids := :PID:) t2
                    ) t3
                WHERE isChild != -1
                ";

        // $sql = "SELECT `name`
        //         FROM (
        //             SELECT t1.id, t1.pid, t1.name,
        //             IF(find_in_set(pid, @pids) > 0, @pids := concat(@pids, ', ', id), -1) AS isChild
        //             FROM
        //                 (SELECT id, pid, name FROM app_doc_category) t1,
        //                 (SELECT @pids := :PID:) t2
        //             ) t3
        //         WHERE isChild != -1
        //         ";

        $query = $this->query($sql, [
            'PID' => $pid,
        ]);
        $result = $query->getResultArray();

        if ($include_self) {
            $builder2 = $this->select($fields_sql_a);
            $builder2->where('id', $pid);
            $db2 = $builder2->findAll();
            if (!empty($db2)) {
                array_unshift($result, $db2[0]);
            }
        }

        return $result;
    }

    public function getAll(array $fields = null)
    {
        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, pid, name, alias, code, created_at, updated_at';
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

    /**
     * 输入pid，输出子节点，默认不包括指定pid的父节点
     *
     * @author freeair
     * @DateTime 2023-03-27
     * @param array|null $fields
     * @param integer $pid
     * @param boolean $include_parent
     * @return void
     */
    // public function getChildrenByPid(array $fields = null, $pid = 0, $include_parent = false)
    // {
    //     $selectSql = '';
    //     if (empty($fields)) {
    //         $selectSql = 'id, pid, name, code';
    //     } else {
    //         //! 注意
    //         if (in_array('id', $fields) === false) {
    //             array_push($fields, 'id');
    //         }
    //         foreach ($fields as $name) {
    //             $selectSql = $selectSql . $name . ', ';
    //         }
    //     }
    //     $selectSql = trim($selectSql, ', ');

    //     $result      = [];
    //     $condition[] = (string) $pid;
    //     do {
    //         $builder = $this->select($selectSql);
    //         $builder->whereIn('pid', $condition);
    //         $db = $builder->findAll();
    //         unset($condition);
    //         foreach ($db as $k => $v) {
    //             $result[]    = $v;
    //             $condition[] = (string) $v['id'];
    //         }
    //     } while (!empty($condition));

    //     if ($include_parent) {
    //         $builder2 = $this->select($selectSql);
    //         $builder2->where('id', $pid);
    //         $db2 = $builder2->findAll();
    //         if (!empty($db2)) {
    //             array_unshift($result, $db2[0]);
    //         }
    //     }

    //     return $result;
    // }

}
