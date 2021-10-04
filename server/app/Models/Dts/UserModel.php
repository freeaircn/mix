<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-04 21:31:26
 */

namespace App\Models\Dts;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_user';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'password', 'forceChgPwd', 'avatar', 'dept_ids', 'job', 'title', 'politic', 'last_login', 'ip_address'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getUserWhereId($columnName = [], $id = [])
    {
        if (empty($columnName) || empty($id)) {
            return [];
        }

        $selectSql = '';
        foreach ($columnName as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $builder->whereIn('id', $id);
        $db = $builder->findAll();

        // $res = [];
        // foreach ($db as $value) {
        //     $res[] = $value['username'];
        // }

        return $db;
    }
}
