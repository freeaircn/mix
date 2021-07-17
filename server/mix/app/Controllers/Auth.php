<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-17 13:00:45
 */

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\AvatarModel;
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

class Auth extends BaseController
{
    use ResponseTrait;

    public function login()
    {
        // 检查请求数据
        if (!$this->validate('AuthLogin')) {
            $errors = $this->validator->getErrors();
            foreach ($errors as $key => $value) {
                log_message('error', '{file}:{line} --> validate request failed ' . $key . ' => ' . $value);
            }
            $res['code'] = 1;
            return $this->respond($res);
        }

        // 取出请求数据
        $client   = $this->request->getJSON(true);
        $phone    = $client['phone'];
        $password = $client['password'];

        // 检查尝试login请求次数
        $ip_address = $this->request->getIPAddress();
        $authModel  = new AuthModel();
        $result     = $authModel->isMaxLoginAttemptsExceeded($phone, $ip_address);
        if ($result) {
            log_message('error', '{file}:{line} --> max login attempts exceeded: ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . ' => ' . $ip_address);

            $res['code'] = 1;
            $res['msg']  = '已尝试失败多次，请5分钟后再试！';
            return $this->respond($res);
        }

        // 查询用户phone是否存在
        $query = $this->getUserByQuery(['phone' => $phone]);
        if ($query['total'] == 0) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            log_message('error', '{file}:{line} --> phone not existed ' . $phone . ' => ' . $ip_address);

            $res['code'] = 1;
            $res['msg']  = '用户不存在！';
            return $this->respond($res);
        }

        $user = $query['result'][0];
        // 检查用户status 被禁用
        if ($user['status'] === '0') {
            $res['code'] = 1;
            $res['msg']  = '该用户已禁用！';
            return $this->respond($res);
        }

        // 验证密码
        $userModel    = new UserModel();
        $hashPassword = $userModel->getUserPasswordByPhone($phone);

        $utils  = service('mixUtils');
        $result = $utils->verifyPassword($password, $hashPassword);
        if ($result === false) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            log_message('error', '{file}:{line} --> password not correct ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . ' => ' . $ip_address);

            $res['code'] = 1;
            $res['msg']  = '账号或密码错误！';
            return $this->respond($res);
        }

        // 清除登录失败记录
        $authModel->clearLoginAttempts($phone, $ip_address);

        // 更新用户登陆成功时间
        $userModel->updateLastLoginAndIP($user['id'], $ip_address);

        // 查询用户拥有的访问权限
        $userRoleModel = new UserRoleModel();
        $roleId        = $userRoleModel->getUserRole($user['id']);

        $roleMenuModel = new RoleMenuModel();
        $menuId        = $roleMenuModel->getMenuIdForAuthority($roleId);

        $menuModel  = new MenuModel();
        $userACL    = $menuModel->getApiAclByMenuId($menuId);
        $userPageId = $menuModel->getPageIdByMenuId($menuId);

        // 取用户头像文件
        if (isset($user['avatar']) && is_numeric($user['avatar'])) {
            $avatarModel = new AvatarModel();
            $avatar      = $avatarModel->getAvatarPathAndNameById($user['avatar']);
            if (isset($avatar['path']) && isset($avatar['name'])) {
                $user['avatarFile'] = $avatar['path'] . $avatar['name'];
            }
        }

        // 写入session数据
        $sessionData           = $user;
        $sessionData['acl']    = $userACL;
        $sessionData['pageId'] = $userPageId;
        $this->session->set($sessionData);

        // 日志
        log_message('notice', '{file}:{line} --> login success ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . ' => ' . $ip_address);

        $res['code'] = 0;
        $res['data'] = ['token' => md5(time())];

        return $this->respond($res);
    }

    public function logout()
    {
        // 日志
        $phone = $this->session->get('phone');
        log_message('notice', '{file}:{line} --> logout ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

        // 销毁session
        $this->session->destroy();

        $res['code'] = 0;
        return $this->respond($res);
    }

    public function getUserInfo()
    {
        // 检验session是否存在，对于 get() 方法，如果你要访问的项目不存在，返回 NULL
        // $phone = $this->session->get('phone');

        // if (is_null($phone)) {
        //     $res['code'] = 1;
        //     $res['msg']  = '账号没有登录！';
        //     return $this->respond($res);
        // }

        // 取session保存的用户数据
        $sessionData = $this->session->get();

        // 取用户有权访问的前端页面路由
        if (isset($sessionData['pageId']) && !empty($sessionData['pageId'])) {
            $pageId = $sessionData['pageId'];

            $model  = new MenuModel();
            $result = $model->getMenu(['pageId' => $pageId]);

            // 去掉acl和pageId
            if (isset($sessionData['acl'])) {
                $sessionData['acl'] = '';
            }
            $sessionData['pageId'] = '';
            array_shift($sessionData);
            array_pop($sessionData);

            $res['code'] = 0;
            $res['data'] = ['menus' => $result, 'info' => $sessionData];
        } else {
            $res['code'] = 1;
            $res['msg']  = '用户没有授权！';
        }

        return $this->respond($res);
    }

    // 角色
    public function getRole()
    {
        $model  = new RoleMode();
        $result = $model->getRole();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

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

    // 岗位
    public function getJob()
    {
        $model  = new JobModel();
        $result = $model->getJob();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

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

    // 政治面貌
    public function getPolitic()
    {
        $model  = new PoliticModel();
        $result = $model->getPolitic();

        $res['code'] = 0;
        $res['data'] = ['data' => $result];

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

    protected function getUserByQuery($queryParam = [])
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
