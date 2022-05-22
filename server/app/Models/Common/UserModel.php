<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-22 17:43:24
 */

namespace App\Models\Common;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup;
    protected $table;

    protected $primaryKey    = 'id';
    protected $allowedFields = ['workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'password', 'forceChgPwd', 'avatar', 'dept_ids', 'job', 'title', 'politic', 'last_login', 'ip_address'];

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
        $this->table   = $config->dbPrefix . 'user';
        parent::__construct();
    }

    public function getUserAllRecords(array $fields = null)
    {
        if (empty($fields)) {
            return [];
        }

        $selectSql = '';
        foreach ($fields as $name) {
            $selectSql = $selectSql . $name . ', ';
        }
        $builder = $this->select($selectSql);

        $db = $builder->findAll();

        return $db;
    }

    public function getUserRecordById(array $fields = null, string $id = null)
    {
        if (empty($id)) {
            return [];
        }

        if (floor($id) != $id) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, workID, username, sex, IdCard, phone, email, status, avatar, dept_ids, job, title, politic, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('id', $id);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function getUserRecordsByIds(array $fields = null, array $ids = null)
    {
        if (empty($ids)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, workID, username, sex, IdCard, phone, email, status, avatar, dept_ids, job, title, politic, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->whereIn('id', $ids);
        $db = $builder->findAll();

        return $db;
    }

    public function getUserRecordByPhone(array $fields = null, string $phone = null)
    {
        if (empty($phone)) {
            return [];
        }

        if (floor($phone) != $phone) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, workID, username, sex, IdCard, phone, email, status, avatar, dept_ids, job, title, politic, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('phone', $phone);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    public function getUserRecordByUsername(array $fields = null, string $username = null)
    {
        if (empty($username)) {
            return [];
        }

        $selectSql = '';
        if (empty($fields)) {
            $selectSql = 'id, workID, username, sex, IdCard, phone, email, status, avatar, dept_ids, job, title, politic, updated_at';
        } else {
            foreach ($fields as $key) {
                $selectSql = $selectSql . $key . ', ';
            }
        }

        $builder = $this->select($selectSql);
        $builder->where('username', $username);
        $db = $builder->findAll();

        return isset($db[0]) ? $db[0] : [];
    }

    // public function getUserByStation($fields = [], $station = '')
    // public function getUserRecordsByStation($fields = [], $station = '')
    // {
    //     if (empty($fields) || empty($station)) {
    //         return [];
    //     }

    //     $selectSql = '';
    //     foreach ($fields as $name) {
    //         $selectSql = $selectSql . $name . ', ';
    //     }
    //     $builder = $this->select($selectSql);

    //     $builder->like('dept_ids', $station);
    //     $db = $builder->findAll();

    //     return $db;
    // }

    public function getUserPwdByUid(string $uid = null)
    {
        if (empty($uid)) {
            return '';
        }

        if (floor($uid) != $uid) {
            return '';
        }

        $db = $this->select('password')
            ->where('id', $uid)
            ->findAll();

        return isset($db[0]) ? $db[0]['password'] : '';
    }

    public function getUserPwdByPhone(string $phone = null)
    {
        if (empty($phone)) {
            return '';
        }

        if (floor($phone) != $phone) {
            return '';
        }

        $db = $this->select('password')
            ->where('phone', $phone)
            ->findAll();

        return isset($db[0]) ? $db[0]['password'] : '';
    }

    public function getUserPwdByEmail(string $email = null)
    {
        if (empty($email)) {
            return '';
        }

        $db = $this->select('password')
            ->where('email', $email)
            ->findAll();

        return isset($db[0]) ? $db[0]['password'] : '';
    }

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

    public function getUserEmailByPhone(string $phone = null)
    {
        if (empty($phone)) {
            return '';
        }

        if (floor($phone) != $phone) {
            return '';
        }

        $db = $this->select('email')
            ->where('phone', $phone)
            ->findAll();

        return isset($db[0]) ? $db[0]['email'] : '';
    }

    public function getUserAvatarByUid(string $uid = null)
    {
        if (empty($uid)) {
            return 0;
        }

        if (floor($uid) != $uid) {
            return 0;
        }

        $db = $this->select('avatar')
            ->where('id', $uid)
            ->findAll();

        return isset($db[0]) ? $db[0]['avatar'] : 0;
    }

    public function createSingleUserRecord(array $data = null)
    {
        if (empty($data)) {
            return false;
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

        // 密码hash
        if (isset($user['password'])) {
            helper('my_auth');
            $tempPwd = my_hash_password($user['password']);
            if ($tempPwd === false) {
                return false;
            }
            $user['password'] = $tempPwd;
        }

        $result = $this->insert($user);
        if (is_numeric($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public function updateSingleUserRecord(array $data = [])
    {
        if (empty($data)) {
            return false;
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

        // 密码hash
        if (isset($user['password'])) {
            helper('my_auth');
            $tempPwd = my_hash_password($user['password']);
            if ($tempPwd === false) {
                return false;
            }
            $user['password'] = $tempPwd;
        }

        $result = $this->save($user);
        return $result;
    }

    public function updateLastLoginAndIpByUid(string $ip_address = null, string $id = null)
    {
        if (empty($id) || empty($ip_address)) {
            return false;
        }

        if (floor($id) != $id) {
            return false;
        }

        $datetime = new \DateTime;
        $data     = [
            'last_login' => $datetime->format('Y-m-d H:i:s'),
            'ip_address' => $ip_address,
        ];
        return $this->update($id, $data);
    }

    public function updateUserPwdByPhone(string $password = null, string $phone = null)
    {
        if (empty($phone) || empty($password)) {
            return false;
        }

        if (floor($phone) != $phone) {
            return false;
        }

        // hash 密码
        helper('my_auth');
        $hashPassword = my_hash_password($password);
        if ($hashPassword === false) {
            return false;
        }

        $data = ['password' => $hashPassword];

        return $this->where('phone', $phone)->set($data)->update();
    }
}
