<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-17 13:01:39
 */

namespace App\Controllers;

use App\Models\DeptModel;
use App\Models\JobModel;
use App\Models\MenuModel;
use App\Models\PoliticModel;
use App\Models\RoleMenuModel;
use App\Models\RoleMode;
use App\Models\TitleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use CodeIgniter\API\ResponseTrait;

class Home extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // return view('welcome_message');
        return view('home.html');
    }

    public function login()
    {
        $client = $this->request->getJSON(true);
        $this->session->set($client);

        $token = md5('666');

        $res['code'] = 0;
        $res['data'] = ['token' => $token, 'client' => $client];

        return $this->respond($res);
    }

    public function logout()
    {
        // 1 log
        // $user = $this->session->userdata();
        // $this->common_tools->app_log('notice', "logout.", 'auth-logout');

        // 2 销毁session
        $this->session->destroy();

        $res['code'] = 0;
        return $this->respond($res);
    }

    // public function getUserInfo()
    // {
    //     $model  = new MenuModel();
    //     $result = $model->getMenu(['type' => '1']);

    //     $res['code'] = 0;
    //     $res['data'] = ['menus' => $result];

    //     return $this->respond($res);
    // }

    // 角色
    public function getRole()
    {
        $model  = new RoleMode();
        $result = $model->getRole();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updateRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 菜单
    public function getMenu()
    {
        $model  = new MenuModel();
        $result = $model->getMenuByColumnName(['id', 'type', 'pid', 'title']);

        $res['code'] = 0;
        $res['data'] = ['menu' => $result];

        return $this->respond($res);
    }

    // 角色-权限
    public function getRoleMenu()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleMenuModel();
        $result = $model->getMenuByRole($role_id);

        $res['code']   = 0;
        $res['data']   = ['menu' => $result];
        $res['client'] = $client;

        return $this->respond($res);
    }

    public function saveRoleMenu()
    {
        $client = $this->request->getJSON(true);

        $role_id = $client['role_id'];
        $menus   = $client['menus'];

        $model  = new RoleMenuModel();
        $result = $model->saveRoleMenu($role_id, $menus);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '权限已更新！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '权限配置失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 部门
    public function getDept()
    {
        $queryParam = $this->request->getGet();

        if (isset($queryParam['columnName'])) {
            $model       = new DeptModel();
            $result      = $model->getDept($queryParam['columnName']);
            $res['data'] = ['data' => $result];
        } else {
            $model       = new DeptModel();
            $result      = $model->getDept();
            $res['data'] = ['data' => $result];
        }

        $res['code'] = 0;

        return $this->respond($res);
    }

    public function newDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updateDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 岗位
    public function getJob()
    {
        $model  = new JobModel();
        $result = $model->getJob();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updateJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 职称
    public function getTitle()
    {
        $model  = new TitleModel();
        $result = $model->getTitle();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updateTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 政治面貌
    public function getPolitic()
    {
        $model  = new PoliticModel();
        $result = $model->getPolitic();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newPolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updatePolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delPolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = 0;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = 1;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 用户
    public function getUser()
    {
        $queryParam = $this->request->getGet();

        // 1 由uid查询单个用户信息
        if (isset($queryParam['uid'])) {
            $model  = new UserModel();
            $result = $model->getUserById($queryParam['uid']);

            $res['code'] = 0;
            $res['data'] = ['data' => $result];

            return $this->respond($res);
        }

        // 2 组合多条件查询：用户名、状态、部门
        $result = $this->getUserList($queryParam);

        $res['code'] = 0;
        $res['data'] = ['total' => $result['total'], 'data' => $result['result']];

        return $this->respond($res);
    }

    public function newUser()
    {
        $client = $this->request->getJSON(true);

        $user = [];
        $role = [];
        // 分离role
        foreach ($client as $key => $value) {
            if ($key === 'role') {
                $role = $value;
            } else {
                $user[$key] = $value;
            }
        }

        // 写入user数据表
        $model = new UserModel();
        $uid   = $model->newUser($user);

        // 写入role数据表
        if (is_numeric($uid)) {
            $model2 = new UserRoleModel();
            $result = $model2->newUserRole($uid, $role);
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
            return $this->respond($res);
        }

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $uid];
        } else {
            $res['code'] = 1;
            $res['msg']  = '添加失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function updateUser()
    {
        $client = $this->request->getJSON(true);

        $user = [];
        $role = [];
        // 分离role
        foreach ($client as $key => $value) {
            if ($key === 'role') {
                $role = $value;
            } else {
                $user[$key] = $value;
            }
        }

        $model = new UserModel();
        $uid   = $model->updateUser($user);

        if ($uid) {
            $model2 = new UserRoleModel();
            $result = $model2->newUserRole($user['id'], $role);
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
            return $this->respond($res);
        }

        if ($result) {
            $res['code'] = 0;
            $res['msg']  = '完成修改！';
            $res['data'] = ['id' => $uid];
        } else {
            $res['code'] = 1;
            $res['msg']  = '修改失败，稍后再试！';
        }

        return $this->respond($res);
    }

    public function delUser()
    {
        $client = $this->request->getJSON(true);
        if (isset($client['id']) && is_numeric($client['id'])) {
            $id = $client['id'];

            $result1 = false;
            $result2 = false;
            // 1 删除user-role表
            $model1  = new UserRoleModel();
            $result1 = $model1->delUserRole($id);

            // 2 删除user表
            if ($result1 === true) {
                $model2  = new UserModel();
                $result2 = $model2->delete($id);
            }

            if ($result1 === true && $result2 === true) {
                $res['code'] = 0;
                $res['msg']  = '完成删除！';
            } else {
                $res['code'] = 1;
                $res['msg']  = '删除失败，稍后再试！';
            }
        } else {
            $res['code'] = 0;
        }

        return $this->respond($res);
    }

    // 用户-角色
    public function getUserRole()
    {
        $tmp = $this->request->getGet();
        $uid = isset($tmp['uid']) ? $tmp['uid'] : '0';

        $model  = new UserRoleModel();
        $result = $model->getUserRole($uid);

        if ($result) {
            $res['code'] = 0;
            $res['data'] = ['data' => $result];
        } else {
            $res['code'] = 0;
            $res['data'] = ['data' => []];
        }

        return $this->respond($res);
    }

    // 内部方法
    protected function getUserList($queryParam = [])
    {
        $deptModel = new DeptModel();
        $dept      = $deptModel->getDept(['id', 'name']);

        $jobModel = new JobModel();
        $job      = $jobModel->getJob(['id', 'name']);

        $titleModel = new titleModel();
        $title      = $titleModel->getTitle(['id', 'name']);

        $politicModel = new politicModel();
        $politic      = $politicModel->getPolitic(['id', 'name']);

        $model  = new UserModel();
        $result = $model->getUserByQueryParam($dept, $job, $title, $politic, $queryParam);

        return $result;
    }
}
