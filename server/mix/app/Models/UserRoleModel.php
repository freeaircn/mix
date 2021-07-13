<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-13 23:00:23
 */

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table = 'app_user_role';
    // protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'role_id'];

    // protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = false;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    public function getUserRole($uid = '0')
    {
        if ($uid === '0') {
            return false;
        }

        $temp = $this->select('role_id')
            ->where('user_id', $uid)
            ->orderBy('role_id', 'ASC')
            ->findAll();

        $res = [];
        foreach ($temp as $value) {
            $res[] = $value['role_id'];
        }

        return $res;
    }

    public function newUserRole($uid = null, $role = [])
    {
        if (!(is_numeric($uid))) {
            return false;
        }

        $this->where('user_id', $uid)->delete();

        $num = count($role);
        $cnt = 0;
        foreach ($role as $v) {
            $data = [
                'user_id' => $uid,
                'role_id' => $v,
            ];
            $this->insert($data);
            $cnt++;
        }

        return ($cnt === $num) ? true : false;
    }

}
