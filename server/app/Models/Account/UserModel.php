<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-11-01 20:40:23
 */

namespace App\Models\Account;

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

    // public function getUserById($id = '0')
    // {
    //     if (!is_numeric($id)) {
    //         return false;
    //     }

    //     $res = $this->select('id, workID, username, sex, IdCard, phone, email, status, dept_ids, job, title, politic')
    //         ->where('id', $id)
    //         ->findAll();

    //     return $res;
    // }

    public function getUserByQueryParam($dept = [], $job = [], $title = [], $politic = [], $queryParam = [])
    {
        $builder = $this->select('id, workID, username, sex, IdCard, phone, email, status, avatar, dept_ids, job, title, politic, updated_at');

        if (isset($queryParam['username']) && $queryParam['username'] !== '') {
            $builder->like('username', $queryParam['username']);
        }
        if (isset($queryParam['phone']) && $queryParam['phone'] !== '') {
            $builder->where('phone', $queryParam['phone']);
        }
        if (isset($queryParam['id']) && $queryParam['id'] !== '') {
            $builder->where('id', $queryParam['id']);
        }
        if (isset($queryParam['status'])) {
            $builder->where('status', $queryParam['status']);
        }
        if (isset($queryParam['department'])) {
            $temp = '+';
            foreach ($queryParam['department'] as $value) {
                $temp = $temp . $value . '+';
            }
            $builder->like('dept_ids', $temp);
        }

        $total = $builder->countAllResults(false);

        $builder->orderBy('id', 'ASC');

        if (isset($queryParam['limit']) && isset($queryParam['offset'])) {
            $res = $builder->findAll($queryParam['limit'], ($queryParam['offset'] - 1) * $queryParam['limit']);
        }
        if (isset($queryParam['limit']) && !isset($queryParam['offset'])) {
            $res = $builder->findAll($queryParam['limit']);
        }
        if (!isset($queryParam['limit']) && !isset($queryParam['offset'])) {
            $res = $builder->findAll();
        }

        if (!empty($res)) {
            foreach ($res as &$user) {
                // 部门名称
                $department = '';
                $temp       = explode("+", trim($user['dept_ids'], '+'));
                $cnt        = count($temp);
                for ($i = 0; $i < $cnt; $i++) {
                    foreach ($dept as $value) {
                        if ($value['id'] === $temp[$i]) {
                            $department = $department . $value['name'] . ' / ';
                        }
                    }
                }
                $user['department'] = rtrim($department, " / ");

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

        return ['total' => $total, 'result' => $res];
    }

    public function getUserPassword(array $whereCondition = [])
    {
        if (empty($whereCondition)) {
            return '';
        }

        $res = $this->select('password')
            ->where($whereCondition)
            ->findAll();

        // return $res[0]['password'];

        if (isset($res[0]) && isset($res[0]['password'])) {
            return $res[0]['password'];
        } else {
            return '';
        }
    }

    // public function getUseEmailByPhone(string $phone = '')
    // {
    //     if (!is_numeric($phone)) {
    //         return '';
    //     }

    //     $res = $this->select('email')
    //         ->where('phone', $phone)
    //         ->findAll();

    //     if (isset($res[0]) && isset($res[0]['email'])) {
    //         return $res[0]['email'];
    //     } else {
    //         return '';
    //     }
    // }

    // public function getUserAvatarById($id = null)
    // {
    //     if (!is_numeric($id)) {
    //         return false;
    //     }

    //     $res = $this->select('avatar')
    //         ->where('id', $id)
    //         ->findAll();

    //     if (isset($res[0]) && isset($res[0]['avatar'])) {
    //         return $res[0]['avatar'];
    //     } else {
    //         return false;
    //     }
    // }

    // public function newUser($data = [])
    // {
    //     if (empty($data)) {
    //         return true;
    //     }

    //     $user       = [];
    //     $department = [];
    //     // 分离department
    //     foreach ($data as $key => $value) {
    //         if ($key === 'department') {
    //             $department = $value;
    //         } else {
    //             $user[$key] = $value;
    //         }
    //     }

    //     // 拼接部门id
    //     $temp = '+';
    //     foreach ($department as $value) {
    //         $temp = $temp . $value . '+';
    //     }
    //     $user['dept_ids'] = $temp;

    //     // 密码hash
    //     if (isset($user['password'])) {
    //         $utils   = service('mixUtils');
    //         $tempPwd = $utils->hashPassword($user['password']);
    //         if ($tempPwd === false) {
    //             return false;
    //         }
    //         $user['password'] = $tempPwd;
    //     }

    //     $result = $this->insert($user);
    //     if (is_numeric($result)) {
    //         return $result;
    //     } else {
    //         return false;
    //     }
    // }

    // public function updateUser($data = [])
    // {
    //     if (empty($data)) {
    //         return true;
    //     }

    //     $user       = [];
    //     $department = [];
    //     // 分离department
    //     foreach ($data as $key => $value) {
    //         if ($key === 'department') {
    //             $department = $value;
    //         } else {
    //             $user[$key] = $value;
    //         }
    //     }

    //     // 拼接部门id
    //     $temp = '+';
    //     foreach ($department as $value) {
    //         $temp = $temp . $value . '+';
    //     }
    //     $user['dept_ids'] = $temp;

    //     // 密码hash
    //     if (isset($user['password'])) {
    //         $utils   = service('mixUtils');
    //         $tempPwd = $utils->hashPassword($user['password']);
    //         if ($tempPwd === false) {
    //             return false;
    //         }
    //         $user['password'] = $tempPwd;
    //     }

    //     $result = $this->save($user);
    //     return $result;
    // }

    // public function updateLastLoginAndIP($id = null, $ip_address = null)
    // {
    //     if (!is_numeric($id) || $id <= 0 || !is_string($ip_address) || empty($ip_address)) {
    //         return false;
    //     }

    //     $datetime = new \DateTime;
    //     $data     = [
    //         'last_login' => $datetime->format('Y-m-d H:i:s'),
    //         'ip_address' => $ip_address,
    //     ];
    //     return $this->update($id, $data);
    // }

    // public function updatePasswordByPhone(string $phone = null, string $password = null)
    // {
    //     if (!is_numeric($phone) || empty($password)) {
    //         return false;
    //     }

    //     // hash 密码
    //     $utils        = \Config\Services::mixUtils();
    //     $hashPassword = $utils->hashPassword($password);
    //     if ($hashPassword === false) {
    //         log_message('error', '{file}:{line} --> update password hash failed' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
    //         return false;
    //     }

    //     // 修改密码
    //     $data = [
    //         'password' => $hashPassword,
    //     ];
    //     if ($this->where('phone', $phone)->set($data)->update()) {
    //         return true;
    //     } else {
    //         log_message('error', '{file}:{line} --> update password db update failed' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
    //         return false;
    //     }
    // }
}
