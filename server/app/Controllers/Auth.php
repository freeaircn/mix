<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-20 23:11:30
 */

namespace App\Controllers;

use App\Models\Admin\ApiModel;
use App\Models\Admin\RoleApiModel;
use App\Models\Admin\RoleDeptModel;
use App\Models\Admin\RoleMenuModel;
use App\Models\Admin\RoleMode;
use App\Models\Admin\RoleWorkflowModel;
use App\Models\Admin\UserRoleModel;
use App\Models\Admin\WorkflowModel;
use App\Models\Auth\AuthModel;
use App\Models\Common\AvatarModel;
use App\Models\Common\DeptModel;
use App\Models\Common\JobModel;
use App\Models\Common\MenuModel;
use App\Models\Common\PoliticModel;
use App\Models\Common\TitleModel;
use App\Models\Common\UserModel;
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
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
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
            $log_text = sprintf('%s max login attempts exceeded %s****%s  => %s', LOG_HEADER, substr($phone, 0, 3), substr($phone, 7, 4), $ip_address);
            log_message('error', $log_text);
            return $this->fail('连续登录失败，五分钟后再登录');
        }

        $user = $this->_getUserByPhone($phone);
        if (empty($user)) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            $log_text = sprintf('%s phone not existed %s => %s', LOG_HEADER, $phone, $ip_address);
            log_message('error', $log_text);
            return $this->fail('手机号码或密码错误');
        }

        $userModel    = new UserModel();
        $hashPassword = $userModel->getUserPwdByPhone($phone);
        $result       = my_verify_password($password, $hashPassword);
        if ($result === false) {
            $authModel->increaseLoginAttempts($phone, $ip_address);
            $log_text = sprintf('%s password not correct %s****%s', LOG_HEADER, substr($phone, 0, 3), substr($phone, 7, 4));
            log_message('error', $log_text);
            return $this->fail('密码或手机号码错误');
        }

        if ($user['status'] === '0') {
            return $this->fail('账号没有激活，请联系管理员');
        }

        $authModel->clearLoginAttempts($phone, $ip_address);
        $userModel->updateLastLoginAndIpByUid($ip_address, $user['id']);

        // 查询用户权限
        $allowPageId        = [];
        $allowApi           = [];
        $allowDefaultDeptId = '0';
        $allowReadDeptId    = [];
        $allowWriteDeptId   = [];
        $allowWorkflow      = [];
        $isAdmin            = false;
        //
        $model   = new UserRoleModel();
        $roleIds = $model->getRoleIdByUid($user['id']);
        if (!empty($roleIds)) {
            $model   = new RoleMenuModel();
            $menuIds = $model->getMenuIdByRoleIds($roleIds);
            if (!empty($menuIds)) {
                $model  = new MenuModel();
                $fields = ['id'];
                $temp   = $model->getMenuRecordsByIds($fields, $menuIds);
                foreach ($temp as $value) {
                    $allowPageId[] = $value['id'];
                }
            }
            //
            $model  = new RoleApiModel();
            $apiIds = $model->getApiIdByRoleIds($roleIds);
            if (!empty($apiIds)) {
                $model = new ApiModel();
                // 读数据库时除掉了辅助树形显示的项type=2
                $fields   = ['api', 'method'];
                $allowApi = $model->getApiRecordsByIds($fields, $apiIds);
            }
            //
            $model = new RoleDeptModel();
            $db    = $model->getDeptRecordsByRoleIds($roleIds);
            foreach ($db as $d) {
                $allowReadDeptId[] = $d['dept_id'];
                if ($d['is_default'] === '1') {
                    $allowDefaultDeptId = $d['dept_id'];
                    $allowWriteDeptId[] = $d['dept_id'];
                    continue;
                }
                if ($d['data_writable'] === '1') {
                    $allowWriteDeptId[] = $d['dept_id'];
                }
            }
            //
            $model = new RoleWorkflowModel();
            $wfIds = $model->getWfIdByRoleIds($roleIds);
            if (!empty($wfIds)) {
                $model = new WorkflowModel();
                // 读数据库时除掉了辅助树形显示的项type=2
                $allowWorkflow = $model->getWorkflowRecordsByIds($wfIds);
            }
        }

        $readDept = [];
        if (!empty($allowReadDeptId)) {
            $model    = new DeptModel();
            $fields   = ['id', 'name'];
            $readDept = $model->getDeptRecordsByIds($fields, $allowReadDeptId);
        }

        $model  = new RoleMode();
        $fields = ['id', 'alias'];
        $roles  = $model->getRoleAllRecords($fields);
        foreach ($roles as $r) {
            foreach ($roleIds as $i) {
                if ($r['id'] === $i && $r['alias'] === 'admin_group') {
                    $isAdmin = true;
                    break;
                }
            }
            if ($isAdmin) {
                break;
            }
        }

        // session
        $sessionData                       = $user;
        $sessionData['allowApi']           = $allowApi;
        $sessionData['allowPageId']        = $allowPageId;
        $sessionData['allowDefaultDeptId'] = $allowDefaultDeptId;
        $sessionData['allowReadDeptId']    = $allowReadDeptId;
        $sessionData['allowWriteDeptId']   = $allowWriteDeptId;
        $sessionData['readDept']           = $readDept;
        $sessionData['allowWorkflow']      = $allowWorkflow;
        $sessionData['isAdmin']            = $isAdmin;

        $this->session->set($sessionData);

        $log_text = sprintf('%s login success [%s] %s****%s => %s', LOG_HEADER, substr(session_id(), 0, 15), substr($phone, 0, 3), substr($phone, 7, 4), $ip_address);
        log_message('notice', $log_text);

        $res['data'] = ['token' => md5(time())];

        return $this->respond($res);
    }

    public function logout()
    {
        $phone    = $this->session->get('phone');
        $log_text = sprintf('%s logout [%s] %s****%s', LOG_HEADER, substr(session_id(), 0, 15), substr($phone, 0, 3), substr($phone, 7, 4));
        log_message('notice', $log_text);

        // 销毁session
        $this->session->destroy();
        // $this->session->stop();

        // return $this->respond([]);
    }

    // 验证码
    public function sms()
    {
        if (!$this->validate('AuthSMS')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $phone  = $client['phone'];

        // 查找绑定的邮箱地址
        $userModel = new UserModel();
        $email     = $userModel->getUserEmailByPhone($phone);

        if (empty($email)) {
            return $this->fail('账号不存在或未绑定邮箱');
        }

        // 生成验证码
        $smsCodeModel = new SmsCodeModel();
        $code         = $smsCodeModel->newSmsCodeByPhone($phone);
        if (empty($code)) {
            $log_text = sprintf('%s new sms code failed %s****%s', LOG_HEADER, substr($phone, 0, 3), substr($phone, 7, 4));
            log_message('error', $log_text);
            return $this->failServerError('服务器发送验证码失败，稍候再试');
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
        $emailAPI->setSubject('【发自Mix】' . $code);
        $emailAPI->setMessage($emailMessage);
        // if (!$emailAPI->send(false)) {
        //     $err      = $emailAPI->printDebugger('subject');
        //     $log_text = sprintf('%s send mail failed %s****%s, %s', LOG_HEADER, substr($phone, 0, 3), substr($phone, 7, 4), $err);
        //     log_message('error', $log_text);

        //     return $this->failServerError('服务器发送验证码失败，稍候再试');
        // }

        // 屏蔽个人信息
        $emailArr = explode("@", $email);
        $strlen   = mb_strlen($emailArr[0], 'utf-8');
        if ($strlen < 4) {
            $maskEmail = $emailArr[0] . '***@' . $emailArr[1];
        } else {
            $maskEmail = substr($emailArr[0], 0, 4) . '***@' . $emailArr[1];
        }

        $res['data'] = ['email' => 'email'];
        return $this->respond($res);
    }

    // 修改密码
    public function resetPassword()
    {
        if (!$this->validate('AuthResetPassword')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client   = $this->request->getJSON(true);
        $phone    = $client['phone'];
        $code     = $client['code'];
        $password = $client['password'];

        // 核对验证码
        $smsCodeModel = new SmsCodeModel();
        $isOK         = $smsCodeModel->validateSmsCodeByPhone($phone, $code);
        if (!$isOK) {
            return $this->fail('验证码无效');
        }

        // 修改密码
        $userModel = new UserModel();
        $isOK      = $userModel->updateUserPwdByPhone($password, $phone);
        if (!$isOK) {
            return $this->failServerError('服务器处理发送错误，稍候再试');
        }

        $res['msg'] = '请使用新密码登录';
        return $this->respond($res);
    }

    // Part - 2
    protected function _getUserByPhone(string $phone = null)
    {
        if (empty($phone)) {
            return [];
        }

        if (floor($phone) != $phone) {
            return [];
        }

        $fields = ['id', 'workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'avatar', 'dept_ids', 'job', 'title', 'politic', 'updated_at'];
        $model  = new UserModel();
        $user   = $model->getUserRecordByPhone($fields, $phone);

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
