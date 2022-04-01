<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 10:02:14
 */

namespace App\Models\Account;

use CodeIgniter\Model;

class AccountModel extends Model
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

    // public function __construct()
    // {
    //     helper('my_auth');
    // }

    public function updateUserInfo($data = [])
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

        // 拼接部门id
        $temp = '+';
        foreach ($department as $value) {
            $temp = $temp . $value . '+';
        }
        $user['dept_ids'] = $temp;

        $result = $this->save($user);
        return $result;
    }

    public function updatePasswordByPhone(string $phone = null, string $password = null)
    {
        if (!is_numeric($phone) || empty($password)) {
            return false;
        }

        // hash 密码
        helper('my_auth');
        $hashPassword = my_hash_password($password);
        if ($hashPassword === false) {
            log_message('error', '{file}:{line} --> update password hash failed');
            return false;
        }

        // 修改密码
        $data = [
            'password' => $hashPassword,
        ];
        if ($this->where('phone', $phone)->set($data)->update()) {
            return true;
        } else {
            log_message('error', '{file}:{line} --> update password db update failed');
            return false;
        }
    }

    public function updatePasswordById(string $id = null, string $password = null)
    {
        if (!is_numeric($id) || empty($password)) {
            return false;
        }

        // hash 密码
        helper('my_auth');
        $hashPassword = my_hash_password($password);
        if ($hashPassword === false) {
            log_message('error', '{file}:{line} --> update password, hash failed');
            return false;
        }

        // 修改密码
        $data = [
            'password' => $hashPassword,
        ];
        if ($this->where('id', $id)->set($data)->update()) {
            return true;
        } else {
            log_message('error', '{file}:{line} --> update password, db update failed');
            return false;
        }
    }

    public function updatePhoneById(string $id = null, string $phone = null)
    {
        if (!is_numeric($id) || !is_numeric($phone)) {
            return false;
        }

        // 修改手机号
        $data = [
            'phone' => $phone,
        ];
        if ($this->where('id', $id)->set($data)->update()) {
            return true;
        } else {
            log_message('error', '{file}:{line} --> update phone, db update failed');
            return false;
        }
    }

    public function updateEmailById(string $id = null, string $email = null)
    {
        if (!is_numeric($id) || empty($email)) {
            return false;
        }

        // 修改邮箱
        $data = [
            'email' => $email,
        ];
        if ($this->where('id', $id)->set($data)->update()) {
            return true;
        } else {
            log_message('error', '{file}:{line} --> update email, db update failed');
            return false;
        }
    }

}
