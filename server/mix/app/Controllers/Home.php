<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-06 21:24:30
 */

namespace App\Controllers;

use App\Models\DeptModel;
use App\Models\JobModel;
use App\Models\MenuModel;
use App\Models\PoliticModel;
use App\Models\RoleMenuMode;
use App\Models\RoleMode;
use App\Models\TitleModel;
use App\Models\UserModel;
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

        $token = md5(time());

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

    public function getUserInfo()
    {
        $menuModel = new MenuModel();
        $menus     = $menuModel->getMenus();

        $res['code'] = 0;
        $res['data'] = ['menus' => $menus];

        return $this->respond($res);
    }

    // 角色
    public function getRole()
    {
        $roleModel = new RoleMode();
        $roles     = $roleModel->getRoles();

        $res['code'] = 0;
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $roles];

        return $this->respond($res);
    }

    public function newRole()
    {
        $client = $this->request->getJSON(true);

        $roleModel = new RoleMode();
        $result    = $roleModel->insert($client);

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

        $roleModel = new RoleMode();
        $result    = $roleModel->save($client);

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

        $roleModel = new RoleMode();
        $result    = $roleModel->delete($client['id']);

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
        $menuModel = new MenuModel();
        $menu      = $menuModel->getMenuIDName();

        $res['code'] = 0;
        $res['data'] = ['menu' => $menu];

        return $this->respond($res);
    }

    // 角色-权限
    public function getRoleMenu()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $roleMenuModel = new RoleMenuMode();
        $menu          = $roleMenuModel->getMenuByRole($role_id);

        $res['code']   = 0;
        $res['data']   = ['menu' => $menu];
        $res['client'] = $client;

        return $this->respond($res);
    }

    public function saveRoleMenu()
    {
        $client = $this->request->getJSON(true);

        $role_id = $client['role_id'];
        $menus   = $client['menus'];

        $roleMenuModel = new RoleMenuMode();
        $result        = $roleMenuModel->saveRoleMenu($role_id, $menus);

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
        $deptModel = new DeptModel();
        $dept      = $deptModel->getDept();

        $res['code'] = 0;
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $dept];

        return $this->respond($res);
    }

    public function newDept()
    {
        $client = $this->request->getJSON(true);

        $deptModel = new DeptModel();
        $result    = $deptModel->insert($client);

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

        $deptModel = new DeptModel();
        $result    = $deptModel->save($client);

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

        $deptModel = new DeptModel();
        $result    = $deptModel->delete($client['id']);

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
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $result];

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
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $result];

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
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $result];

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
        $jobModel = new JobModel();
        $job      = $jobModel->getJob();

        $titleModel = new titleModel();
        $title      = $titleModel->getTitle();

        $politicModel = new politicModel();
        $politic      = $politicModel->getPolitic();

        $model  = new UserModel();
        $result = $model->getUser($job, $title, $politic);

        $res['code'] = 0;
        $res['data'] = ['pageNo' => 1, 'totalCount' => 2, 'data' => $result];

        return $this->respond($res);
    }

    public function newUser()
    {
        $client = $this->request->getJSON(true);

        $model  = new UserModel();
        $result = $model->newUser($client);

        // $res['code']   = 0;
        // $res['msg']    = '完成添加！';
        // $res['data']   = ['id' => '1'];
        // $res['client'] = $client;

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

    public function updateUser()
    {
        $client = $this->request->getJSON(true);

        $model  = new UserModel();
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

    public function delUser()
    {
        $client = $this->request->getJSON(true);

        $model  = new UserModel();
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
}
