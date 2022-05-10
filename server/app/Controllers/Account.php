<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 21:14:31
 */

namespace App\Controllers;

use App\Models\Account\AccountModel;
use App\Models\Common\AvatarModel;
use App\Models\Common\DeptModel;
use App\Models\Common\JobModel;
use App\Models\Common\MenuModel;
use App\Models\Common\PoliticModel;
use App\Models\Common\TitleModel;
use App\Models\Common\UserModel;
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
        $cid    = $client['id'];
        $sid    = $this->session->get('id');

        if ($cid !== $sid) {
            $res['info']  = 'invalid id';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

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
        $uid   = $model->updateUserSetting($newSetting);
        if (!$uid) {
            $phone = $this->session->get('phone');
            log_message('error', '{file}:{line} --> update user info db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $user = $this->_preSetSession($sid);
        $this->session->set($user);

        $res['msg']  = '保存成功';
        $res['data'] = ['session' => $this->_getUserSession()];

        return $this->respond($res);
    }

    public function updatePassword()
    {
        if (!$this->validate('AccountUpdatePassword')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        $password    = $client['password'];
        $newPassword = $client['newPassword'];

        // 验证密码
        $userModel    = new UserModel();
        $hashPassword = $userModel->getUserPwdByUid($id);

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user password ');
            return $this->fail('旧登录密码错误');
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePasswordById($id, $newPassword)) {
            log_message('error', '{file}:{line} --> update user password db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['msg'] = '修改成功';
        return $this->respond($res);
    }

    public function updatePhone()
    {
        if (!$this->validate('AccountUpdatePhone')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        $password = $client['password'];
        $phone    = $client['phone'];

        // 检查手机号是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPwdByPhone($phone) !== '') {
            return $this->fail('新手机号已被其他用户绑定');
        }

        // 验证密码
        $hashPassword = $userModel->getUserPwdByUid($id);
        $result       = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user phone' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            return $this->fail('密码错误！');
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updatePhoneById($id, $phone)) {
            log_message('error', '{file}:{line} --> update user phone db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));

            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 修改session
        $this->session->set('phone', $phone);

        $res['msg']  = '修改成功';
        $res['data'] = ['phone' => $phone];
        return $this->respond($res);
    }

    // 验证码
    public function sms()
    {
        if (!$this->validate('AccountSMS')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        $email = $client['email'];

        // 检查邮箱是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPwdByEmail($email) !== '') {
            return $this->fail('新邮箱已被其他用户绑定');
        }

        // 生成验证码
        $phone        = $this->session->get('phone');
        $smsCodeModel = new SmsCodeModel();
        $code         = $smsCodeModel->newSmsCodeByPhone($phone);
        if (empty($code)) {
            log_message('error', '{file}:{line} --> new sms code failed' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            return $this->failServerError('服务器处理发生错误，稍候再试');
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
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['data'] = ['email' => $email];
        return $this->respond($res);
    }

    public function updateEmail()
    {
        if (!$this->validate('AccountUpdateEmail')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $this->session->get('id');

        $password = $client['password'];
        $email    = $client['email'];
        $code     = $client['code'];

        // 检查邮箱是否存在
        $userModel = new UserModel();
        if ($userModel->getUserPwdByEmail($email) !== '') {
            return $this->fail('新邮箱已被其他用户绑定');
        }

        // 核对验证码
        $phone        = $this->session->get('phone');
        $smsCodeModel = new SmsCodeModel();
        $isOK         = $smsCodeModel->validateSmsCodeByPhone($phone, $code);
        if (!$isOK) {
            return $this->fail('验证码无效');
        }

        // 验证密码
        $hashPassword = $userModel->getUserPwdByUid($id);

        $result = my_verify_password($password, $hashPassword);
        if ($result === false) {
            log_message('error', '{file}:{line} --> password error when update user email' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            return $this->fail('密码错误');
        }

        // 修改数据库
        $model = new AccountModel();
        if (!$model->updateEmailById($id, $email)) {
            log_message('error', '{file}:{line} --> update user email db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 修改session
        $this->session->set('email', $email);

        $res['msg']  = '修改成功';
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
            return $this->fail('头像图片文件超要求');
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
            return $this->failServerError('头像图片文件调整失败');
        }

        // 查询头像文件路径和文件名，待后续删除旧头像文件
        $avatarId    = $this->session->get('avatar');
        $avatarModel = new AvatarModel();
        $oldAvatar   = $avatarModel->getAvatarPathAndNameById($avatarId);

        // 更改数据库
        if (!$avatarModel->updateAvatarById($relativePath, $newName, $avatarId)) {
            log_message('error', '{file}:{line} --> upload avatar db failed ' . '[' . substr(session_id(), 0, 15) . '] ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4));
            // 删除文件
            unlink($absoluteFile);
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 修改session
        $avatarFile = $relativePath . $newName;
        $this->session->set('avatarFile', $avatarFile);

        // 删除旧文件
        if (!empty($oldAvatar)) {
            if (strpos($oldAvatar['path'], 'default') === false) {
                $old = $absolutePath . $oldAvatar['name'];
                if (file_exists($old)) {
                    unlink($absolutePath . $oldAvatar['name']);
                }
            }
        }

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
        $db    = $model->getMenuRecordsByIds($fields, $pageId);
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
        $result['politic'] = $politicModel->getPoliticAllRecords(['id', 'name', 'status']);

        $deptModel      = new DeptModel();
        $result['dept'] = $deptModel->getDeptAllRecords(['id', 'name', 'pid', 'status']);

        $jobModel      = new JobModel();
        $result['job'] = $jobModel->getJobAllRecords(['id', 'name', 'status']);

        $titleModel      = new titleModel();
        $result['title'] = $titleModel->getTitleAllRecords(['id', 'name', 'status']);

        $res['http_status'] = 200;
        $res['data']        = $result;
        return $res;

    }

    protected function _preSetSession(string $uid = null)
    {
        if (empty($uid)) {
            return [];
        }

        if (floor($uid) != $uid) {
            return [];
        }

        $fields = ['id', 'workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'avatar', 'dept_ids', 'job', 'title', 'politic', 'updated_at'];
        $model  = new UserModel();
        $user   = $model->getUserRecordById($fields, $uid);

        if (empty($user)) {
            return [];
        }

        $model = new DeptModel();
        $dept  = $model->getDeptAllRecords(['id', 'name']);

        $model = new JobModel();
        $job   = $model->getJobAllRecords(['id', 'name']);

        $model = new titleModel();
        $title = $model->getTitleAllRecords(['id', 'name']);

        $model   = new politicModel();
        $politic = $model->getPoliticAllRecords(['id', 'name']);

        // 部门名称
        $department = '';
        $deptIds    = explode("+", trim($user['dept_ids'], '+'));
        $cnt        = count($deptIds);
        for ($i = 0; $i < $cnt; $i++) {
            foreach ($dept as $value) {
                if ($value['id'] === $deptIds[$i]) {
                    $department = $department . $value['name'] . ' / ';
                }
            }
        }
        $user['department'] = rtrim($department, " / ");
        $user['dept_ids']   = $deptIds;

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

        // 头像文件
        if (isset($user['avatar']) && is_numeric($user['avatar'])) {
            $avatarModel = new AvatarModel();
            $avatar      = $avatarModel->getAvatarPathAndNameById($user['avatar']);
            if (isset($avatar['path']) && isset($avatar['name'])) {
                $user['avatarFile'] = $avatar['path'] . $avatar['name'];
            }
        }

        return $user;
    }
}
