<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-20 19:08:23
 */

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\AvatarModel;
use App\Models\DeptModel;
use App\Models\JobModel;
use App\Models\MenuModel;
use App\Models\PoliticModel;
use App\Models\SmsCodeModel;
use App\Models\TitleModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class Account extends BaseController
{
    use ResponseTrait;

    public function getUserInfo()
    {
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

            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['menus' => $result, 'info' => $sessionData];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '用户没有授权！';
        }

        return $this->respond($res);
    }

    public function updateUserInfo()
    {
        // 检查请求数据
        if (!$this->validate('AccountUpdateUserInfo')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $sid    = $this->session->get('id');

        // 取出检验后的数据
        $userInfo = [
            'id'         => $sid,
            'username'   => $client['username'],
            'sex'        => $client['sex'],
            'IdCard'     => $client['IdCard'],
            'politic'    => $client['politic'],
            'job'        => $client['job'],
            'title'      => $client['title'],
            'department' => $client['department'],
        ];

        // 修改数据库
        $model = new AccountModel();
        $uid   = $model->updateUserInfo($userInfo);
        if (!$uid) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user info db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '保存失败，稍后再试！';
            return $this->respond($res);
        }

        // 读数据库
        $query = $this->getUserByQuery(['id' => $sid]);

        $user = $query['result'][0];
        // 取用户头像文件
        if (isset($user['avatar']) && is_numeric($user['avatar'])) {
            $avatarModel = new AvatarModel();
            $avatar      = $avatarModel->getAvatarPathAndNameById($user['avatar']);
            if (isset($avatar['path']) && isset($avatar['name'])) {
                $user['avatarFile'] = $avatar['path'] . $avatar['name'];
            }
        }

        // 修改session
        $this->session->set($user);

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '保存成功！';
        $res['data'] = ['info' => $user];

        return $this->respond($res);
    }

    public function updatePassword()
    {
        // 检查请求数据
        if (!$this->validate('AccountUpdatePassword')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        // 取出检验后的数据
        $password    = $client['password'];
        $newPassword = $client['newPassword'];

        // 验证密码
        $userModel    = new UserModel();
        $hashPassword = $userModel->getUserPassword(['id' => $id]);

        $utils  = service('mixUtils');
        $result = $utils->verifyPassword($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user password ');

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '旧登录密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePasswordById($id, $newPassword)) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user password db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试！';
            return $this->respond($res);
        }

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '修改成功！';

        return $this->respond($res);
    }

    public function updatePhone()
    {
        // 检查请求数据
        if (!$this->validate('AccountUpdatePhone')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        // 取出检验后的数据
        $password = $client['password'];
        $phone    = $client['phone'];

        // 检查手机号是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPassword(['phone' => $phone]) !== '') {

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '新手机号已被其他用户绑定！';
            return $this->respond($res);
        }

        // 验证密码
        $hashPassword = $userModel->getUserPassword(['id' => $id]);

        $utils  = service('mixUtils');
        $result = $utils->verifyPassword($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user phone');

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePhoneById($id, $phone)) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user phone db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试！';
            return $this->respond($res);
        }

        // 修改session
        $this->session->set('phone', $phone);

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '修改成功！';
        $res['data'] = ['phone' => $phone];

        return $this->respond($res);
    }

    // 验证码
    public function sms()
    {
        // 检查请求数据
        if (!$this->validate('AccountSMS')) {
            $res['error'] = $this->validator->getErrors();
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效！';
            return $this->respond($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        // 取出请求数据
        $email = $client['email'];

        // 检查邮箱是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPassword(['email' => $email]) !== '') {

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '新邮箱已被其他用户绑定！';
            return $this->respond($res);
        }

        // 生成验证码
        $phone        = $this->session->get('phone');
        $smsCodeModel = new SmsCodeModel();
        $code         = $smsCodeModel->newSmsCodeByPhone($phone);
        if (empty($code)) {
            log_message('error', '{file}:{line} --> new sms code failed' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code']  = EXIT_ERROR;
            $res['msg']   = '发送验证码失败，稍后尝试！';
            $res['error'] = 'new sms code';
            return $this->respond($res);
        }

        // 验证码发送邮件
        $emailParam = [
            'code' => $code,
            'dt'   => date("Y-m-d H:i:s"),
        ];
        $emailMessage = view('auth/sms_code.php', $emailParam);

        $emailAPI = \Config\Services::email();
        $emailAPI->setFrom($emailAPI->SMTPUser, '来自Mix应用');
        $emailAPI->setTo($email);
        $emailAPI->setSubject('【请勿回复此邮件】验证码： ' . $code);
        $emailAPI->setMessage($emailMessage);
        if (!$emailAPI->send(false)) {
            $err = $emailAPI->printDebugger('subject');
            log_message('error', '{file}:{line} --> send mail failed' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . '.  ' . $err);

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '发送验证码失败，稍后尝试！';
            return $this->respond($res);
        }

        // 屏蔽个人信息

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['email' => $email];
        return $this->respond($res);
    }

    public function updateEmail()
    {
        // 检查请求数据
        if (!$this->validate('AccountUpdateEmail')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        // 取出检验后的数据
        $password = $client['password'];
        $email    = $client['email'];
        $code     = $client['code'];

        // 检查邮箱是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPassword(['email' => $email]) !== '') {

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '新邮箱已被其他用户绑定！';
            return $this->respond($res);
        }

        // 核对验证码
        $phone        = $this->session->get('phone');
        $smsCodeModel = new SmsCodeModel();
        $isOK         = $smsCodeModel->validateSmsCodeByPhone($phone, $code);
        if (!$isOK) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '验证码无效！';
            return $this->respond($res);
        }

        // 验证密码
        $hashPassword = $userModel->getUserPassword(['id' => $id]);

        $utils  = service('mixUtils');
        $result = $utils->verifyPassword($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user email');

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updateEmailById($id, $email)) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user email db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试！';
            return $this->respond($res);
        }

        // 修改session
        $this->session->set('email', $email);

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '修改成功！';
        $res['data'] = ['email' => $email];

        return $this->respond($res);
    }

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
