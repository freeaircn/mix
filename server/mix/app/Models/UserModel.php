<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-14 00:44:17
 */

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table         = 'app_user';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'password', 'forceChgPwd', 'avatar',
        'deptLev0', 'deptLev1', 'deptLev2', 'deptLev3', 'deptLev4', 'deptLev5', 'deptLev6', 'deptLev7', 'job', 'title', 'politic'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getAllUser($dept = [], $job = [], $title = [], $politic = [])
    {
        $res = $this->select('id, workID, username, sex, IdCard, phone, email, status, deptLev0, deptLev1, deptLev2, deptLev3, deptLev4, deptLev5, deptLev6, deptLev7, job, title, politic, updated_at')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (!empty($res)) {
            foreach ($res as &$user) {
                // 部门名称
                $department = '';
                for ($cnt = 0; $cnt < 8; $cnt++) {
                    $index = 'deptLev' . strval($cnt);
                    if ($user[$index] !== 0) {
                        foreach ($dept as $value) {
                            if ($value['id'] === $user[$index]) {
                                $department = $department . $value['name'] . ' / ';
                            }
                        }
                    }
                }
                $user['department'] = substr($department, 0, strlen($department) - 3);

                // 岗位名称
                foreach ($job as $x) {
                    if ($user['job'] === $x['id']) {
                        $user['jobLabel'] = $x['name'];
                    }
                }
                // 职称名称
                foreach ($title as $y) {
                    if ($user['title'] === $y['id']) {
                        $user['titleLabel'] = $y['name'];
                    }
                }
                // 政治面貌
                foreach ($politic as $z) {
                    if ($user['politic'] === $z['id']) {
                        $user['politicLabel'] = $z['name'];
                    }
                }
            }
        }

        return $res;
    }

    public function getUserById($id = '0')
    {
        $res = $this->select('id, workID, username, sex, IdCard, phone, email, status, deptLev0, deptLev1, deptLev2, deptLev3, deptLev4, deptLev5, deptLev6, deptLev7, job, title, politic')
            ->where('id', $id)
            ->findAll();

        return $res;
    }

    public function newUser($data = [])
    {
        if (empty($data)) {
            return true;
        }

        $user       = [];
        $department = [];
        // 分离department
        foreach ($data as $key => $value) {
            if ($key === 'department') {
                $department = $value;
            } else {
                $user[$key] = $value;
            }
        }

        // 提取department
        for ($index = 0; $index < 8; $index++) {
            $key        = 'deptLev' . $index;
            $user[$key] = '0';
        }
        foreach ($department as $index => $value) {
            $key        = 'deptLev' . $index;
            $user[$key] = $value;
        }

        // 密码hash
        $utils   = service('mixUtils');
        $tempPwd = $utils->hashPassword($user['password']);
        if ($tempPwd === false) {
            return false;
        }
        $user['password'] = $tempPwd;

        $result = $this->insert($user);
        if (is_numeric($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public function updateUser($data = [])
    {
        if (empty($data)) {
            return true;
        }

        $user       = [];
        $department = [];
        // 分离department
        foreach ($data as $key => $value) {
            if ($key === 'department') {
                $department = $value;
            } else {
                $user[$key] = $value;
            }
        }

        // 提取department
        for ($index = 0; $index < 8; $index++) {
            $key        = 'deptLev' . $index;
            $user[$key] = '0';
        }
        foreach ($department as $index => $value) {
            $key        = 'deptLev' . $index;
            $user[$key] = $value;
        }

        $result = $this->save($user);
        return $result;
    }
}
