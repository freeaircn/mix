<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-12 21:08:59
 */

namespace App\Controllers;

use App\Models\Admin\ApiModel;
use App\Models\Admin\AvatarModel;
use App\Models\Admin\DeptModel;
use App\Models\Admin\EquipmentUnitModel;
use App\Models\Admin\JobModel;
use App\Models\Admin\MenuModel;
use App\Models\Admin\PoliticModel;
use App\Models\Admin\RoleApiModel;
use App\Models\Admin\RoleDeptModel;
use App\Models\Admin\RoleMenuModel;
use App\Models\Admin\RoleMode;
use App\Models\Admin\RoleWorkflowModel;
use App\Models\Admin\TitleModel;
use App\Models\Admin\UserModel;
use App\Models\Admin\UserRoleModel;
use App\Models\Admin\WorkflowModel;
use CodeIgniter\API\ResponseTrait;

class Admin extends BaseController
{
    use ResponseTrait;

    // 角色
    public function getRole()
    {
        $model  = new RoleMode();
        $result = $model->getRole();

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newRole()
    {
        $client = $this->request->getJSON(true);

        $model  = new RoleMode();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 菜单
    public function getMenu()
    {
        $params = $this->request->getGet();

        $model = new MenuModel();
        if (isset($params['any']) && $params['any'] === 'any') {
            $columnName = ['id', 'pid', 'title'];
        } else {
            $columnName = ['id', 'pid', 'title', 'path', 'redirect', 'name', 'component', 'hidden', 'hideChildrenInMenu', 'meta_hidden', 'icon', 'keepAlive', 'hiddenHeaderContent', 'permission', 'target', 'updated_at'];

        }
        $result = $model->getMenus($columnName);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已修改';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function delMenu()
    {
        $client = $this->request->getJSON(true);

        $model  = new MenuModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    // API
    public function getApi()
    {
        $model      = new ApiModel();
        $columnName = ['id', 'type', 'pid', 'title', 'api', 'method', 'updated_at'];
        $result     = $model->getApis($columnName);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已修改';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function delApi()
    {
        $client = $this->request->getJSON(true);

        $model  = new ApiModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    // Workflow
    public function getWorkflow()
    {
        $model      = new WorkflowModel();
        $columnName = ['id', 'type', 'pid', 'name', 'workflow', 'method', 'updated_at'];
        $result     = $model->getWorkflow($columnName);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已添加';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已修改';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function delWorkflow()
    {
        $client = $this->request->getJSON(true);

        $model  = new WorkflowModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 角色-权限
    public function getRoleMenu()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleMenuModel();
        $result = $model->getMenuByRole($role_id);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '权限已更新';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '权限配置失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getRoleApi()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleApiModel();
        $result = $model->getByRoleId($role_id);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function saveRoleApi()
    {
        $client = $this->request->getJSON(true);

        $role_id = $client['role_id'];
        $api     = $client['api'];

        $model  = new RoleApiModel();
        $result = $model->saveRoleApi($role_id, $api);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '权限已更新';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '权限配置失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getRoleDept()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $result = [];
        if (!isset($client['method'])) {
            $model  = new RoleDeptModel();
            $result = $model->getByRoleId($role_id);
        }

        if (isset($client['method']) && $client['method'] === 'set') {
            $model  = new RoleDeptModel();
            $result = $model->getByRoleId2($role_id);
            $deptId = [];
            foreach ($result as $r) {
                $deptId[] = $r['dept_id'];
            }

            $model      = new DeptModel();
            $columnName = ['id', 'name'];
            $dept       = $model->getByIds($columnName, $deptId);

            foreach ($result as $key => $r) {
                foreach ($dept as $d) {
                    if ($r['dept_id'] === $d['id']) {
                        $result[$key]['name'] = $d['name'];
                    }
                }
            }
        }

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function saveRoleDept()
    {
        $client = $this->request->getJSON(true);

        if (!isset($client['method'])) {
            $role_id = $client['role_id'];
            $dept    = $client['dept'];
            //
            $model = new RoleDeptModel();
            $db    = $model->getByRoleId($role_id);
            $old   = [];
            foreach ($db as $value) {
                $old[] = $value['dept_id'];
            }

            $result = $model->saveRoleDept($role_id, $dept);
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '权限已更新';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '权限配置失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getRoleWorkflow()
    {
        $client = $this->request->getGet();

        $role_id = $client['role_id'];

        $model  = new RoleWorkflowModel();
        $result = $model->getByRoleId($role_id);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function saveRoleWorkflow()
    {
        $client = $this->request->getJSON(true);

        $role_id  = $client['role_id'];
        $workflow = $client['workflow'];

        $model  = new RoleWorkflowModel();
        $result = $model->saveRoleWorkflow($role_id, $workflow);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '权限已更新';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '权限配置失败，稍后再试';
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

        $res['code'] = EXIT_SUCCESS;

        return $this->respond($res);
    }

    public function newDept()
    {
        $client = $this->request->getJSON(true);

        $model  = new DeptModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 岗位
    public function getJob()
    {
        $model  = new JobModel();
        $result = $model->getJob();

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newJob()
    {
        $client = $this->request->getJSON(true);

        $model  = new JobModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 职称
    public function getTitle()
    {
        $model  = new TitleModel();
        $result = $model->getTitle();

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newTitle()
    {
        $client = $this->request->getJSON(true);

        $model  = new TitleModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试！';
        }

        return $this->respond($res);
    }

    // 政治面貌
    public function getPolitic()
    {
        $model  = new PoliticModel();
        $result = $model->getPolitic();

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    public function newPolitic()
    {
        $client = $this->request->getJSON(true);

        $model  = new PoliticModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成删除！';
        } else {
            $res['code'] = EXIT_ERROR;
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

            $result[0]['department'] = explode("+", trim($result[0]['dept_ids'], '+'));
            unset($result[0]['dept_ids']);

            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['data' => $result];

            return $this->respond($res);
        }

        // 2 组合多条件查询：用户名、状态、部门
        $result = $this->getUserList($queryParam);

        $res['code'] = EXIT_SUCCESS;
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
        if (!is_numeric($avatarId)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试！';
            $res['err']  = 'avatar';
            return $this->respond($res);
        }
        $user['avatar'] = $avatarId;

        // 写入user数据表
        $model = new UserModel();
        $uid   = $model->newUser($user);

        // 写入role数据表
        if (is_numeric($uid)) {
            $model2 = new UserRoleModel();
            $result = $model2->newUserRole($uid, $role);
        } else {
            // 头像 db表，回退
            $avatarModel->deleteAvatarById($avatarId);

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试！';
            return $this->respond($res);
        }

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成添加！';
            $res['data'] = ['id' => $uid];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试！';
            return $this->respond($res);
        }

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '完成修改！';
            $res['data'] = ['id' => $uid];
        } else {
            $res['code'] = EXIT_ERROR;
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
            $result3 = false;
            // 删除user-role表
            $model1  = new UserRoleModel();
            $result1 = $model1->delUserRole($id);

            // 删除user表
            if ($result1 === true) {
                $model2   = new UserModel();
                $avatarId = $model2->getUserAvatarById($id);
                $result2  = $model2->delete($id);
            }

            // 删除头像
            if ($result2 === true && $avatarId !== false) {
                $model3 = new AvatarModel();
                $avatar = $model3->getAvatarPathAndNameById($avatarId);
                // 删除文件
                if (strpos($avatar['path'], 'default') === false) {
                    $absolutePath = WRITEPATH . '../public/avatar/user/';
                    unlink($absolutePath . $avatar['name']);
                }
                $result3 = $model3->deleteAvatarById($avatarId);
            }

            if ($result1 === true && $result2 === true) {
                $res['code'] = EXIT_SUCCESS;
                $res['msg']  = '完成删除！';
            } else {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '删除失败，稍后再试！';
            }
        } else {
            $res['code'] = EXIT_SUCCESS;
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
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['data' => $result];
        } else {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['data' => []];
        }

        return $this->respond($res);
    }

    // 设备单元
    public function getEquipmentUnit()
    {
        $params = $this->request->getGet();

        $model = new EquipmentUnitModel();

        $columnName = ['id', 'pid', 'name', 'description', 'updated_at'];

        if (isset($params['select'])) {
            $query = [
                'id >' => 0,
            ];
        } else {
            $query = [
                'id >' => 1,
            ];
        }

        $result = $model->get($columnName, $query);

        if (empty($result)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请稍后查询';
        } else {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = $result;
        }

        return $this->respond($res);
    }

    public function newEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->insert($client);

        if (is_numeric($result)) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '新建成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '新建失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->save($client);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function deleteEquipmentUnit()
    {
        $client = $this->request->getJSON(true);

        $model  = new EquipmentUnitModel();
        $result = $model->delete($client['id']);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
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
