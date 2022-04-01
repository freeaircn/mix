<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 09:55:02
 */

namespace App\Controllers;

use App\Models\Admin\AvatarModel;
use App\Models\Admin\DeptModel;
use App\Models\Admin\JobModel;
use App\Models\Admin\MenuModel;
use App\Models\Admin\PoliticModel;
use App\Models\Admin\RoleMenuModel;
use App\Models\Admin\TitleModel;
use App\Models\Admin\UserModel;
use App\Models\Admin\UserRoleModel;
use App\Models\Auth\AuthModel;
use App\Models\Auth\RoleWorkflowAuthorityModel;
use App\Models\Auth\WorkflowAuthorityModel;
use App\Models\SmsCodeModel;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('my_auth');
    }

    public function login()
    {
        // 检查请求数据
        if (!$this->validate('AuthLogin')) {
            $res['code'] = EXIT_ERROR;
            // $res['msg']  = '请求数据无效！';
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

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '多次登录错误，请5分钟后再试';
            return $this->respond($res);
        }

        // 查询用户phone是否存在
        $query = $this->getUserByQuery(['phone' => $phone]);
        if ($query['total'] == 0) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            log_message('error', '{file}:{line} --> phone not existed ' . $phone . ' => ' . $ip_address);

            $res['code'] = EXIT_ERROR;
            // $res['msg']  = '用户不存在';
            return $this->respond($res);
        }

        $user = $query['result'][0];
        // 检查用户status 被禁用
        if ($user['status'] === '0') {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '用户未激活，请联系管理员';
            return $this->respond($res);
        }

        // 验证密码
        $userModel    = new UserModel();
        $hashPassword = $userModel->getUserPassword(['phone' => $phone]);

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            log_message('error', '{file}:{line} --> password not correct ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . ' => ' . $ip_address);

            $res['code'] = EXIT_ERROR;
            // $res['msg']  = '账号或密码错误！';
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

        // 查询用户拥有的流程权限
        $model         = new RoleWorkflowAuthorityModel();
        $wfAuthorityId = $model->getByRoles($roleId);

        $model       = new WorkflowAuthorityModel();
        $columnName  = ['alias'];
        $query       = $wfAuthorityId;
        $wfAuthority = $model->getAliasByWhereInId($columnName, $query);

        // 取用户头像文件
        if (isset($user['avatar']) && is_numeric($user['avatar'])) {
            $avatarModel = new AvatarModel();
            $avatar      = $avatarModel->getAvatarPathAndNameById($user['avatar']);
            if (isset($avatar['path']) && isset($avatar['name'])) {
                $user['avatarFile'] = $avatar['path'] . $avatar['name'];
            }
        }

        // 获取用户所属部门
        $deptIds          = explode("+", trim($user['dept_ids'], '+'));
        $belongToDeptId   = '0';
        $belongToDeptName = '';

        $deptModel = new DeptModel();
        $belongTo  = $deptModel->getDept(['id', 'name', 'dataMask'], ['ids' => $deptIds]);
        foreach ($belongTo as $value) {
            if ($value['dataMask'] != 0) {
                $belongToDeptId   = $value['id'];
                $belongToDeptName = $value['name'];
            }
        }

        // 写入session数据
        $user['dept_ids']                = explode("+", trim($user['dept_ids'], '+'));
        $sessionData                     = $user;
        $sessionData['acl']              = $userACL;
        $sessionData['pageId']           = $userPageId;
        $sessionData['wfAuthority']      = $wfAuthority;
        $sessionData['belongToDeptId']   = $belongToDeptId;
        $sessionData['belongToDeptName'] = $belongToDeptName;
        $this->session->set($sessionData);

        // 日志
        log_message('notice', '{file}:{line} --> login success ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . ' => ' . $ip_address);

        $res['code'] = EXIT_SUCCESS;
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

        $res['code'] = EXIT_SUCCESS;
        return $this->respond($res);
    }

    // 验证码
    public function sms()
    {
        // 检查请求数据
        if (!$this->validate('AuthSMS')) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取出请求数据
        $client = $this->request->getJSON(true);
        $phone  = $client['phone'];

        // 查找绑定的邮箱地址
        $userModel = new UserModel();
        $email     = $userModel->getUseEmailByPhone($phone);

        if (empty($email)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '账号不存在或未绑定邮箱！';
            return $this->respond($res);
        }

        // 生成验证码
        $smsCodeModel = new SmsCodeModel();
        $code         = $smsCodeModel->newSmsCodeByPhone($phone);
        if (empty($code)) {
            log_message('error', '{file}:{line} --> new sms code failed' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '发送验证码失败，稍后尝试！';
            return $this->respond($res);
        }

        // 验证码发送邮件
        $emailParam = [
            'code' => $code,
            'dt'   => date("Y-m-d H:i:s"),
        ];
        $emailMessage = view('auth/sms_code.php', $emailParam);

        $emailAPI = \Config\Services::email();
        $emailAPI->setFrom($emailAPI->SMTPUser);
        $emailAPI->setTo($email);
        $emailAPI->setSubject('【发自Mix，请勿回复】验证码： ' . $code);
        $emailAPI->setMessage($emailMessage);
        if (!$emailAPI->send(false)) {
            $err = $emailAPI->printDebugger('subject');
            log_message('error', '{file}:{line} --> send mail failed ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . '.  ' . $err);

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '发送验证码失败，稍后尝试！';
            return $this->respond($res);
        }

        // 屏蔽个人信息
        $emailArr = explode("@", $email);
        $strlen   = mb_strlen($emailArr[0], 'utf-8');
        if ($strlen < 4) {
            $maskEmail = $emailArr[0] . '***@' . $emailArr[1];
        } else {
            $maskEmail = substr($emailArr[0], 0, 4) . '***@' . $emailArr[1];
        }

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['email' => $maskEmail];
        return $this->respond($res);
    }

    // 修改密码
    public function resetPassword()
    {
        // 检查请求数据
        if (!$this->validate('AuthResetPassword')) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取出请求数据
        $client   = $this->request->getJSON(true);
        $phone    = $client['phone'];
        $code     = $client['code'];
        $password = $client['password'];

        // 核对验证码
        $smsCodeModel = new SmsCodeModel();
        $isOK         = $smsCodeModel->validateSmsCodeByPhone($phone, $code);
        if (!$isOK) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '验证码无效！';
            return $this->respond($res);
        }

        // 修改密码
        $userModel = new UserModel();
        $isOK      = $userModel->updatePasswordByPhone($phone, $password);
        if (!$isOK) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改密码失败，稍后再试！';
            return $this->respond($res);
        }

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '请使用新密码登录！';
        return $this->respond($res);
    }

    // public function getUserInfo()
    // {
    //     // 取session保存的用户数据
    //     $sessionData = $this->session->get();

    //     // 取用户有权访问的前端页面路由
    //     if (isset($sessionData['pageId']) && !empty($sessionData['pageId'])) {
    //         $pageId = $sessionData['pageId'];

    //         $model  = new MenuModel();
    //         $result = $model->getMenu(['pageId' => $pageId]);

    //         // 去掉acl和pageId
    //         if (isset($sessionData['acl'])) {
    //             $sessionData['acl'] = '';
    //         }
    //         $sessionData['pageId'] = '';
    //         array_shift($sessionData);
    //         array_pop($sessionData);

    //         $res['code'] = EXIT_SUCCESS;
    //         $res['data'] = ['menus' => $result, 'info' => $sessionData];
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '用户没有授权！';
    //     }

    //     return $this->respond($res);
    // }

    // 内部方法
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
