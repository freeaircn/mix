<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-27 20:47:50
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-06 21:30:02
 */

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup = 'mix';

    protected $table      = 'app_user';
    protected $primaryKey = 'id';
    // protected $allowedFields = ['name', 'status', 'description'];

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getUser($job = [], $title = [], $politic = [])
    {
        $res = $this->select('id, workID, username, sex, phone, email, status, department, job, title, politic, updated_at')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (!empty($res)) {
            foreach ($res as &$user) {
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

    public function newUser($data = [])
    {
        if (empty($data)) {
            return true;
        }

        $user = [];
        $role = [];
        foreach ($data as $key => $value) {
            if ($key !== 'role') {
                $user[$key] = $value;
            } else {
                $role = $value;
            }
        }

    }
}
