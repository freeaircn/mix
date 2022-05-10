<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-10 22:43:51
 */

namespace App\Controllers;

use App\Models\Admin\ApiModel;
use App\Models\Admin\EquipmentUnitModel;
use App\Models\Admin\RoleApiModel;
use App\Models\Admin\RoleDeptModel;
use App\Models\Admin\RoleMenuModel;
use App\Models\Admin\RoleMode;
use App\Models\Admin\RoleWorkflowModel;
use App\Models\Admin\UserRoleModel;
use App\Models\Admin\WorkflowModel;
use App\Models\Common\AvatarModel;
use App\Models\Common\DeptModel;
use App\Models\Common\JobModel;
use App\Models\Common\MenuModel;
use App\Models\Common\PoliticModel;
use App\Models\Common\TitleModel;
use App\Models\Common\UserModel;
use CodeIgniter\API\ResponseTrait;

class Admin extends BaseController
{
    use ResponseTrait;

    // 角色
    public function getRole()
    {
        $model  = new RoleMode();
        $result = $model->getRoleAllRecords();

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 菜单
    public function getMenu()
    {
        $params = $this->request->getGet();

        $model = new MenuModel();
        if (isset($params['any']) && $params['any'] === 'any') {
            $fields = ['id', 'pid', 'title'];
        } else {
            $fields = ['id', 'pid', 'title', 'path', 'redirect', 'name', 'component', 'hidden', 'hideChildrenInMenu', 'meta_hidden', 'icon', 'keepAlive', 'hiddenHeaderContent', 'permission', 'target', 'updated_at'];

        }
        $result = $model->getMenuAllRecords($fields);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // API
    public function getApi()
    {
        $model  = new ApiModel();
        $fields = ['id', 'type', 'pid', 'title', 'api', 'method', 'updated_at'];
        $result = $model->getApiAllRecords($fields);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // Workflow
    public function getWorkflow()
    {
        $model  = new WorkflowModel();
        $fields = ['id', 'type', 'pid', 'name', 'workflow', 'method', 'updated_at'];
        $result = $model->getWorkflowAllRecords($fields);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
            $res['msg'] = '删除失败，稍后再试';
        }
    }

    // 角色-权限
    public function getRoleMenu()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleMenuModel();
        $result = $model->getMenuIdByRoleId($role_id);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function saveRoleMenu()
    {
        $client = $this->request->getJSON(true);

        $role_id = $client['role_id'];
        $menus   = $client['menus'];

        $model  = new RoleMenuModel();
        $result = $model->saveRoleMenuRecordsByRoleId($menus, $role_id);

        if ($result === true) {
            $res['msg'] = '权限已更新';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function getRoleApi()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleApiModel();
        $result = $model->getApiIdByRoleId($role_id);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function saveRoleApi()
    {
        $client = $this->request->getJSON(true);

        $role_id = $client['role_id'];
        $api     = $client['api'];

        $model  = new RoleApiModel();
        $result = $model->saveRoleApiRecordsByRoleId($api, $role_id);

        if ($result === true) {
            $res['msg'] = '权限已更新';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function getRoleDept()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $result = [];
        if (!isset($client['method'])) {
            $model  = new RoleDeptModel();
            $fields = ['dept_id'];
            $result = $model->getDeptIdByRoleId($fields, $role_id);
        }

        if (isset($client['method']) && $client['method'] === 'set') {
            $model   = new RoleDeptModel();
            $fields  = ['role_id', 'dept_id', 'data_writable', 'is_default'];
            $result  = $model->getDeptIdByRoleId($fields, $role_id);
            $deptIds = [];
            foreach ($result as $r) {
                $deptIds[] = $r['dept_id'];
            }

            $model  = new DeptModel();
            $fields = ['id', 'name'];
            $dept   = $model->getDeptRecordsByIds($fields, $deptIds);

            foreach ($result as $key => $r) {
                foreach ($dept as $d) {
                    if ($r['dept_id'] === $d['id']) {
                        $result[$key]['name'] = $d['name'];
                    }
                }
            }
        }

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function saveRoleDept()
    {
        $client = $this->request->getJSON(true);

        if (!isset($client['method'])) {
            $role_id = $client['role_id'];
            $new     = $client['dept'];
            //
            $model  = new RoleDeptModel();
            $fields = ['dept_id'];
            $db     = $model->getDeptIdByRoleId($fields, $role_id);
            $old    = [];
            foreach ($db as $value) {
                $old[] = $value['dept_id'];
            }

            $delete = [];
            $add    = [];
            foreach ($old as $o) {
                $found = false;
                foreach ($new as $n) {
                    if ($o === $n) {
                        $found = true;
                        break;
                    }
                }
                if ($found === false) {
                    $delete[] = $o;
                }
            }

            foreach ($new as $n) {
                $found = false;
                foreach ($old as $o) {
                    if ($o === $n) {
                        $found = true;
                        break;
                    }
                }
                if ($found === false) {
                    $add[] = $n;
                }
            }

            $result = $model->updateRoleDept($role_id, $delete, $add);
        }

        if (isset($client['method']) && $client['method'] === 'set') {
            $role_id = $client['role_id'];
            $dept_id = $client['dept_id'];
            $data    = [
                'data_writable' => $client['data_writable'],
                'is_default'    => $client['is_default'],
            ];
            $model  = new RoleDeptModel();
            $result = $model->setRoleDept($role_id, $dept_id, $data);
        }

        if ($result === true) {
            $res['msg'] = '权限已更新';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function getRoleWorkflow()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleWorkflowModel();
        $result = $model->getWfIdByRoleId($role_id);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function saveRoleWorkflow()
    {
        $client = $this->request->getJSON(true);

        $role_id  = $client['role_id'];
        $workflow = $client['workflow'];

        $model  = new RoleWorkflowModel();
        $result = $model->saveRoleWorkflowRecordsByRoleId($workflow, $role_id);

        if ($result === true) {
            $res['msg'] = '权限已更新';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 部门
    public function getDept()
    {
        $queryParam = $this->request->getGet();

        // if (isset($queryParam['columnName'])) {
        //     $model       = new DeptModel();
        //     $result      = $model->getDeptAllRecords($queryParam['columnName']);
        //     $res['data'] = ['data' => $result];
        // } else {
        $model       = new DeptModel();
        $result      = $model->getDeptAllRecords();
        $res['data'] = ['data' => $result];
        // }

        return $this->respond($res);
    }

    public function newDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 岗位
    public function getJob()
    {
        $model  = new JobModel();
        $result = $model->getJobAllRecords();

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 职称
    public function getTitle()
    {
        $model  = new TitleModel();
        $result = $model->getTitleAllRecords();

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 政治面貌
    public function getPolitic()
    {
        $model  = new PoliticModel();
        $result = $model->getPoliticAllRecords();

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    public function newPolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updatePolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delPolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // 用户
    public function getUser()
    {
        $params = $this->request->getGet();

        // 1 由uid查询单个用户信息
        if (isset($params['uid'])) {
            $fields = ['id', 'workID', 'username', 'sex', 'IdCard', 'phone', 'email', 'status', 'dept_ids', 'job', 'title', 'politic'];
            $model  = new UserModel();
            $db     = $model->getUserRecordById($fields, $params['uid']);

            if (isset($db['dept_ids'])) {
                $db['department'] = explode("+", trim($db['dept_ids'], '+'));
                unset($db['dept_ids']);
            }

            $res['data'] = ['data' => $db];
            return $this->respond($res);
        }

        // 2 组合多条件查询：用户名、状态、部门
        $result = $this->getUserList($params);

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

        // 默认头像
        $avatarModel = new AvatarModel();
        $avatarId    = $avatarModel->newDefaultAvatarBySex($user['sex']);
        if ($avatarId === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
        $user['avatar'] = $avatarId;

        // 写入user数据表
        $model = new UserModel();
        $uid   = $model->createSingleUser($user);

        // 写入role数据表
        if (is_numeric($uid)) {
            $model2 = new UserRoleModel();
            $result = $model2->createSingleUserRecord($uid, $role);
        } else {
            // 头像 db表，回退
            $avatarModel->deleteAvatarById($avatarId);

            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        if ($result) {
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $uid];
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
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

        $model  = new UserModel();
        $result = $model->updateSingleUserRecord($user);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $model2 = new UserRoleModel();
        $result = $model2->createUserRoleRecordsByUid($role, $user['id']);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function delUser()
    {
        $client = $this->request->getJSON(true);
        if (isset($client['id']) && is_numeric($client['id'])) {
            $id = $client['id'];

            $result1 = false;
            $result2 = false;
            $result3 = false;
            // 删除user-role表
            $model1  = new UserRoleModel();
            $result1 = $model1->delUserRoleRecordByUid($id);

            // 删除user表
            if ($result1 === true) {
                $model2   = new UserModel();
                $avatarId = $model2->getUserAvatarByUid($id);
                $result2  = $model2->delete($id);
            }

            // 删除头像
            if ($result2 === true && $avatarId !== 0) {
                $model3 = new AvatarModel();
                $avatar = $model3->getAvatarPathAndNameById($avatarId);
                // 删除文件
                if (!empty($avatar)) {
                    if (strpos($avatar['path'], 'default') === false) {
                        $absolutePath = WRITEPATH . '../public/avatar/user/';
                        unlink($absolutePath . $avatar['name']);
                    }
                    $result3 = $model3->deleteAvatarById($avatarId);
                }
            }

            if ($result1 === true && $result2 === true) {
                $res['msg'] = '已删除';
                return $this->respond($res);
            } else {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
        } else {
            return $this->respond([]);
        }
    }

    // 用户-角色
    public function getUserRole()
    {
        $tmp = $this->request->getGet();
        $uid = isset($tmp['uid']) ? $tmp['uid'] : '0';

        $model  = new UserRoleModel();
        $result = $model->getRoleIdByUid($uid);

        $res['data'] = ['data' => $result];
        return $this->respond($res);
    }

    // 设备单元
    public function getEquipmentUnit()
    {
        $params = $this->request->getGet();

        $model = new EquipmentUnitModel();

        $fields = ['id', 'pid', 'name', 'description', 'updated_at'];

        if (isset($params['select'])) {
            $query = [
                'id >' => 0,
            ];
        } else {
            $query = [
                'id >' => 1,
            ];
        }

        $result = $model->get($fields, $query);

        if (empty($result)) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        } else {
            $res['data'] = $result;
            return $this->respond($res);
        }
    }

    public function newEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['msg'] = '新建成功';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function updateEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->save($client);

        if ($result) {
            $res['msg'] = '已修改';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    public function deleteEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['msg'] = '已删除';
            return $this->respond($res);
        } else {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }
    }

    // Part - 2
    protected function getUserList($queryParam = [])
    {
        $deptModel = new DeptModel();
        $dept      = $deptModel->getDeptAllRecords(['id', 'name']);

        $jobModel = new JobModel();
        $job      = $jobModel->getJobAllRecords(['id', 'name']);

        $titleModel = new titleModel();
        $title      = $titleModel->getTitleAllRecords(['id', 'name']);

        $politicModel = new politicModel();
        $politic      = $politicModel->getPoliticAllRecords(['id', 'name']);

        $model  = new UserModel();
        $result = $model->getUserByQueryParam($dept, $job, $title, $politic, $queryParam);

        return $result;
    }
}
