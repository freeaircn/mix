<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-07 23:21:39
 */

namespace App\Controllers;

use App\Models\Account\AccountModel;
use App\Models\Account\AvatarModel;
use App\Models\Account\DeptModel;
use App\Models\Account\JobModel;
use App\Models\Account\MenuModel;
use App\Models\Account\PoliticModel;
use App\Models\Account\TitleModel;
use App\Models\Account\UserModel;
use App\Models\SmsCodeModel;
use CodeIgniter\API\ResponseTrait;

class Account extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('my_auth');
    }

    public function queryEntry()
    {
        $resource = $this->request->getGet('resource');
        if (empty($resource)) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        switch ($resource) {
            case 'session_and_menus':
                $result = $this->_reqSessionAndMenus();
                break;
            case 'basic_setting_form':
                $result = $this->_reqBasicSettingForm();
                break;
            default:
                $res['error'] = '请求数据无效';
                return $this->fail($res);
        }

        if ($result['http_status'] === 400) {
            return $this->fail($result['msg']);
        }
        if ($result['http_status'] === 401) {
            return $this->failUnauthorized($result['msg']);
        }
        if ($result['http_status'] === 404) {
            return $this->failNotFound($result['msg']);
        }
        if ($result['http_status'] === 500) {
            return $this->failServerError($result['msg']);
        }

        if ($result['http_status'] === 200) {
            $response = [];
            if (isset($result['data'])) {
                $response['data'] = $result['data'];
            }
            if (isset($result['msg'])) {
                $response['msg'] = $result['msg'];
            }
            return $this->respond($response);
        }

        return $this->failServerError('服务器内部错误');
    }

    public function updateBasicSetting()
    {
        // 检查请求数据
        if (!$this->validate('AccountUpdateBasicSetting')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        // 取session的id
        $client = $this->request->getJSON(true);
        $sid    = $this->session->get('id');

        // 取出检验后的数据
        $newSetting = [
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
        $uid   = $model->updateUserInfo($newSetting);
        if (!$uid) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user info db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            return $this->failServerError('服务器处理发生错误，稍候再试');
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
        $user['dept_ids'] = explode("+", trim($user['dept_ids'], '+'));
        $this->session->set($user);

        $res['msg']  = '保存成功';
        $res['data'] = ['session' => $this->_getUserSession()];

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

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user password ');

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '旧登录密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePasswordById($id, $newPassword)) {
            // $phone = $this->session->get('phone');
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

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user phone' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePhoneById($id, $phone)) {
            // $phone = $this->session->get('phone');
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
            log_message('error', '{file}:{line} --> new sms code failed' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

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

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user email' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '密码错误！';
            return $this->respond($res);
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updateEmailById($id, $email)) {
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

    public function updateAvatar()
    {
        $file = $this->request->getFile('file');
        // var_dump($file);

        $phone = $this->session->get('phone');
        // 检查文件
        if (!$file->isValid() || ($file->getSize() / 1024 / 1024 > 2) || $file->getMimeType() !== 'image/jpeg') {
            log_message('error', '{file}:{line} --> upload avatar file invalid ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '头像图片文件超要求';
            return $this->respond($res, 422);

        }

        $newName      = $file->getRandomName();
        $absolutePath = WRITEPATH . '../public/avatar/user/';
        $absoluteFile = WRITEPATH . '../public/avatar/user/' . $newName;
        $relativePath = 'avatar/user/';
        // 移动文件
        if (!$file->hasMoved()) {
            $file->move($absolutePath, $newName, true);
        }

        // 调整文件大小
        try {
            $image = \Config\Services::image('gd')
                ->withFile($absoluteFile)
                ->resize(144, 144, true)
                ->save($absoluteFile);
        } catch (\CodeIgniter\Images\ImageException $e) {
            log_message('error', '{file}:{line} --> upload avatar resize failed ' . $e->getMessage() . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            // 删除文件
            unlink($absoluteFile);
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '头像图片文件调整失败';
            return $this->respond($res, 422);
        }

        // 查询头像文件路径和文件名，待后续删除旧头像文件
        $avatarId    = $this->session->get('avatar');
        $avatarModel = new AvatarModel();
        $oldAvatar   = $avatarModel->getAvatarPathAndNameById($avatarId);

        // 更改数据库
        if (!$avatarModel->updateAvatarById($avatarId, $relativePath, $newName)) {
            log_message('error', '{file}:{line} --> upload avatar db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            // 删除文件
            unlink($absoluteFile);
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改DB失败';
            return $this->respond($res, 422);
        }

        // 修改session
        $avatarFile = $relativePath . $newName;
        $this->session->set('avatarFile', $avatarFile);

        // 删除旧文件
        if (strpos($oldAvatar['path'], 'default') === false) {
            $old = $absolutePath . $oldAvatar['name'];
            if (file_exists($old)) {
                unlink($absolutePath . $oldAvatar['name']);
            }
        }

        $res['code']       = EXIT_SUCCESS;
        $res['avatarFile'] = $avatarFile;

        return $this->respond($res);
    }

    // Part - 2
    protected function _reqSessionAndMenus()
    {
        $userSession = $this->_getUserSession();
        $userMenus   = $this->_getUserMenus();

        $res['http_status'] = 200;
        $res['data']        = ['session' => $userSession, 'menus' => $userMenus];
        return $res;
    }

    protected function _getUserSession()
    {
        $userSession = $this->session->get();
        if (!isset($userSession['phone'])) {
            return [];
        }

        // 过滤 后端字段
        $data = [];
        foreach ($userSession as $key => $value) {
            if (strpos($key, '_ci_') !== false) {
                continue;
            }
            if ($key === 'allowApi') {
                continue;
            }
            if ($key === 'allowPageId') {
                continue;
            }
            if ($key === 'allowReadDeptId') {
                continue;
            }
            if ($key === 'allowWriteDeptId') {
                continue;
            }
            if ($key === 'allowWorkflow') {
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    protected function _getUserMenus()
    {
        $userSession = $this->session->get();
        if (empty($userSession['allowPageId'])) {
            return [];
        }

        $pageId = $userSession['allowPageId'];
        $fields = ['id', 'pid', 'name', 'path', 'component', 'redirect', 'hideChildrenInMenu', 'title', 'icon', 'keepAlive', 'meta_hidden', 'hiddenHeaderContent', 'hidden', 'permission', 'target'];

        $model = new MenuModel();
        $db    = $model->getByPageIds($fields, $pageId);
        if (empty($db)) {
            return [];
        }

        $menus = $this->_adaptMenus($db);

        return $menus;
    }

    protected function _adaptMenus(array $db = null)
    {
        if (empty($db)) {
            return [];
        }

        $menus = [];
        foreach ($db as $item) {
            $menu = [];
            $meta = [];
            //
            $menu['id']   = $item['id'];
            $menu['pid']  = $item['pid'];
            $menu['name'] = $item['name'];
            $menu['path'] = $item['path'];
            if ($item['component'] !== '') {
                $menu['component'] = $item['component'];
            }
            if ($item['redirect'] !== '') {
                $menu['redirect'] = $item['redirect'];
            }
            $menu['hidden']             = $item['hidden'];
            $menu['hideChildrenInMenu'] = $item['hideChildrenInMenu'];
            // if ($item['hidden'] === '1') {
            //     $menu['hidden'] = '1';
            // }
            // if ($item['hideChildrenInMenu'] === '1') {
            //     $menu['hideChildrenInMenu'] = '1';
            // }
            //
            $meta['title'] = $item['title'];
            if ($item['icon'] !== '') {
                $meta['icon'] = $item['icon'];
            }
            if ($item['keepAlive'] === '1') {
                $meta['keepAlive'] = '1';
            }
            if ($item['meta_hidden'] === '1') {
                $meta['hidden'] = '1';
            }
            if ($item['hiddenHeaderContent'] === '1') {
                $meta['hiddenHeaderContent'] = '1';
            }
            if ($item['permission'] !== '') {
                $meta['permission'] = [];
            }
            if ($item['target'] !== '') {
                $meta['target'] = $item['target'];
            }
            //
            $menu['meta'] = $meta;
            $menus[]      = $menu;
        }

        helper('my_array');
        $res = my_arr2tree($menus);

        return $res;
    }

    protected function _reqBasicSettingForm()
    {
        $result = [
            'politic' => [],
            'dept'    => [],
            'job'     => [],
            'title'   => [],
        ];

        $politicModel      = new politicModel();
        $result['politic'] = $politicModel->getPolitic(['id', 'name', 'status']);

        $deptModel      = new DeptModel();
        $result['dept'] = $deptModel->getDept(['id', 'name', 'pid', 'status']);

        $jobModel      = new JobModel();
        $result['job'] = $jobModel->getJob(['id', 'name', 'status']);

        $titleModel      = new titleModel();
        $result['title'] = $titleModel->getTitle(['id', 'name', 'status']);

        $res['http_status'] = 200;
        $res['data']        = $result;
        return $res;

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
