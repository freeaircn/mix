<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-08 17:04:46
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Libraries\Workflow\Dts\WfDts;
use App\Libraries\Workflow\Ticket;
use App\Models\Dts\DeptModel;
use App\Models\Dts\DeviceModel;
use App\Models\Dts\DtsModel;
use App\Models\Dts\RoleWorkflowAuthorityModel;
use App\Models\Dts\UserModel;
use App\Models\Dts\UserRoleModel;
use App\Models\Dts\WorkflowAuthModel;
use CodeIgniter\API\ResponseTrait;

class Dts extends BaseController
{
    use ResponseTrait;

    // 模板
    protected $blankTpl;
    protected $updateTpl;
    // 单号末尾几位
    protected $ticketIdTailLength;
    //
    protected $keyValueMap;

    public function __construct()
    {
        $this->blankTpl  = "【现象】\n\n【时间】\n\n【影响】\n\n【已采取措施】\n\n";
        $this->updateTpl = "【当前进展】\n\n【下一步计划】\n\n";

        $this->ticketIdTailLength = 3;

        $this->keyValueMap = [
            'type'  => [
                1 => '隐患',
                2 => '故障',
            ],
            'level' => [
                1 => '紧急',
                2 => '严重',
                3 => '一般',
            ],
        ];
    }

    public function getBlankForm()
    {
        // $params = $this->request->getGet();

        // 检查请求数据
        // if (!$this->validate('DtsGetBlankForm')) {
        //     $res['error'] = $this->validator->getErrors();

        //     $res['code'] = EXIT_ERROR;
        //     $res['msg']  = '请求数据无效';
        //     return $this->respond($res);
        // }

        // $station_id  = $params['station_id'];
        $station_id  = session('ownDirectDataDeptId');
        $description = $this->blankTpl;
        $deviceList  = $this->_getDeviceList($station_id);

        if (empty($deviceList)) {
            $res['code'] = EXIT_ERROR;
        } else {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = [
                'description' => $description,
                'deviceList'  => $deviceList,
            ];
        }

        return $this->respond($res);
    }

    public function postTicketAttachment()
    {
        # code...
    }

    public function postDraft()
    {
        // 检查请求数据
        if (!$this->validate('DtsPostDraft')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);
        //
        $uid        = session('id');
        $station_id = session('ownDirectDataDeptId');
        $draft      = [
            'station_id'  => $station_id,
            'type'        => $client['type'],
            'title'       => $client['title'],
            'level'       => $client['level'],
            'device'      => $client['device'],
            'description' => $client['description'],
            'creator_id'  => $uid,
        ];

        $wf                = new WfDts();
        $draft['place_at'] = $wf->getFirstPlace();

        $draft['status'] = 'publish';
        // dts_id
        $maker  = new MyIdMaker();
        $dts_id = $maker->apply('DTS');
        if ($dts_id === false) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '失败，稍后再试';

            return $this->respond($res);
        }
        $draft['dts_id'] = $dts_id;

        $model  = new DtsModel();
        $result = $model->insertDB($draft);
        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '创建问题单';

        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getList()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('DtsGetList')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query['station_id'] = session('allowDeptId');
        $query['status']     = 'publish';
        $query['limit']      = $param['limit'];
        $query['offset']     = $param['offset'];
        $columnName          = ['id', 'dts_id', 'station_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator_id'];

        $model = new DtsModel();
        $db    = $model->getByLimitOffset($columnName, $query);

        if ($db['total'] === 0) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = $db;
            return $this->respond($res);
        }

        // id => name
        $data = $this->_getUserNameAndWorkflowName($db['data']);
        $data = $this->_getDeptName($data);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['total' => $db['total'], 'data' => $data];
        return $this->respond($res);
    }

    public function getDetails()
    {
        // 检查请求数据
        if (!$this->validate('DtsGetDetails')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $params = $this->request->getGet();
        $dts_id = $params['dts_id'];

        $columnName = ['dts_id', 'type', 'title', 'level', 'station_id', 'place_at', 'device', 'description', 'progress', 'created_at', 'updated_at', 'creator_id', 'reviewer_id'];
        $query      = ['dts_id' => $dts_id];

        $model = new DtsModel();
        $db    = $model->getByDtsId($columnName, $query);
        if (empty($db)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '问题单不存在';
            return $this->respond($res);
        }

        $details        = $db;
        $newProgressTpl = $this->updateTpl;

        // id => name
        $details['type']  = $this->keyValueMap['type'][$db['type']];
        $details['level'] = $this->keyValueMap['level'][$db['level']];

        $users_id = [
            'creator'  => $db['creator_id'],
            'reviewer' => $db['reviewer_id'],
        ];
        $users               = $this->_getUserName($users_id);
        $details['creator']  = $users['creator'];
        $details['reviewer'] = $users['reviewer'];

        $details['device']  = $this->_getDeviceFullName($db['device']);
        $details['station'] = $this->_getDeptName2($db['station_id']);

        $steps = $this->_getStepsBar($details['creator'], $details['reviewer'], $db['created_at'], $db['updated_at'], $db['place_at']);

        // 进度
        // $wf                  = new WfDts();
        // $meta                = $wf->getPlaceMetadata($place);
        // $details['place_at'] = $meta['name'];
        // $wfPlaceAuth         = $meta['auth'];

        // 用户权限
        $view = [
            'isCreator' => false,
            'isCheck'   => false,
            'isReview'  => false,
        ];
        $handlers  = [];
        $reviewers = [];

        // $uid            = session('id');
        // $ownWfAuthority = session('wfAuthority');

        // if ($wf->isCheckPlace($place)) {
        //     if ($handler === $uid || in_array($wfPlaceAuth, $ownWfAuthority)) {
        //         $view = $wf->getPlaceMetaOfView($place);
        //         $temp = $this->getHandler($station_id, $place);
        //         foreach ($temp as $value) {
        //             if ($value['id'] !== $handler) {
        //                 $handlers[] = $value;
        //             }
        //         }

        //         $temp = $this->getHandler($station_id, $wf->getReviewPlace());
        //         foreach ($temp as $value) {
        //             if ($value['id'] !== $handler) {
        //                 $reviewers[] = $value;
        //             }
        //         }
        //     }
        // }

        //
        $res['code'] = EXIT_SUCCESS;
        $res['data'] = [
            'details'        => $details,
            'steps'          => $steps,
            'newProgressTpl' => $newProgressTpl,
            'view'           => $view,
            'handlers'       => $handlers,
            'reviewers'      => $reviewers,
        ];
        // $res['extra'] = $view;

        return $this->respond($res);
    }

    public function updateProgress()
    {
        // 检查请求数据
        if (!$this->validate('DtsUpdateProgress')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);
        $dts_id = $client['dts_id'];

        $model      = new DtsModel();
        $columnName = ['id', 'dts_id', 'station_id'];
        $query      = ['dts_id' => $dts_id];
        $db         = $model->getByDtsId($columnName, $query);
        if (empty($db)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求无效';
            return $this->respond($res);
        }

        $stationId = $this->session->get('dept_id');
        if ($stationId !== $db['station_id']) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '没有权限修改';
            $res['x1']   = $stationId;
            $res['x2']   = $db['station_id'];
            return $this->respond($res);
        }

        $progress = $this->_getContentTplHead() . $client['progress'];

        // $result = $model->updateProgress($progress, $where);
        $result = true;
        if ($result !== false) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '更新进展成功';
            // $res['data'] = $result;
        } else {
            $res['code'] = EXIT_ERROR;
        }

        return $this->respond($res);
    }

    public function putTicketHandler()
    {
        // 检查请求数据
        if (!$this->validate('DtsHandlerPut')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client  = $this->request->getJSON(true);
        $handler = $client['handler'];
        $where   = [
            'ticket_id' => $client['ticket_id'],
        ];

        $model  = new TicketModel();
        $result = $model->updateHandler($handler, $where);
        if ($result) {
            $res['code'] = EXIT_SUCCESS;
        } else {
            $res['code'] = EXIT_ERROR;
        }

        return $this->respond($res);
    }

    public function postTicketToReview()
    {
        // 检查请求数据
        if (!$this->validate('DtsToReviewPost')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client    = $this->request->getJSON(true);
        $ticket_id = $client['ticket_id'];

        $model = new TicketModel();

        $columnName = ['id', 'place_at'];
        $query      = ['ticket_id' => $ticket_id];
        $db         = $model->getByTicketId($columnName, $query);

        if (empty($db)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '问题单不存在';
            return $this->respond($res);
        }

        $ticket = new Ticket($db['place_at']);
        $wf     = new WorkflowCore();

        $wf->bindTicket($ticket);
        if ($wf->toReview() === false) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '提交失败，稍后再试';
            return $this->respond($res);
        }

        // $db['place_at'] = $ticket->getCurrentPlace();
        $data = [
            'id'       => $db['id'],
            'place_at' => $ticket->getCurrentPlace(),
            'reviewer' => $client['reviewer'],
        ];
        $result = $model->myUpdate($data);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '提交审核成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '提交失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 内部方法
    protected function getProgressTemplate($type = 'draft')
    {
        return $this->progressTemplate[$type];
    }

    protected function getHandler($station_id = 0, $place = '')
    {
        if (!is_numeric($station_id) || empty($place)) {
            return [];
        }

        $wf       = new WorkflowCore();
        $metadata = $wf->getPlaceMetadata($place);
        if (!isset($metadata['auth'])) {
            return [];
        }

        // 获取流程权限
        $model      = new WorkflowAuthModel();
        $columnName = ['id'];
        $query      = ['alias' => $metadata['auth']];
        $wfAuthId   = $model->getByAlias($columnName, $query);

        // 获取角色，多个id
        $model  = new RoleWorkflowAuthorityModel();
        $roleId = $model->getByWFAuthority($wfAuthId['id']);

        // 获取用户Id
        $model  = new UserRoleModel();
        $userId = $model->getWhereInRole($roleId);

        // 获取用户
        $model      = new UserModel();
        $columnName = ['id', 'username', 'status', 'dept_ids'];
        $db         = $model->getUserWhereId($columnName, $userId);

        // 过滤
        $handler = [];
        $cnt     = count($db);
        $find    = '+' . $station_id . '+';
        for ($i = 0; $i < $cnt; $i++) {
            if (strpos($db[$i]['dept_ids'], $find) !== false) {
                $handler[] = [
                    'id'       => $db[$i]['id'],
                    'username' => $db[$i]['username'],
                    'status'   => $db[$i]['status'],
                ];
            }
        }

        return $handler;
    }

    protected function _getDeviceList(int $station_id = 0)
    {
        if ($station_id <= 0) {
            return [];
        }
        $list = [];

        $model      = new DeviceModel();
        $columnName = ['id', 'pid', 'name'];
        $query      = ['id >' => 1];
        $list       = $model->get($columnName, $query);

        return $list;
    }

    protected function _getContentTplHead()
    {
        return date('Y-m-d H:i', time()) . ' ' . session('username') . "\n";
    }

    protected function _getUserNameAndWorkflowName(array $array = null)
    {
        if (empty($array)) {
            return [];
        }

        $model      = new UserModel();
        $columnName = ['id', 'username', 'status'];
        $users      = $model->getUsers($columnName);

        $wf = new WfDts();

        $cnt = count($array);
        for ($i = 0; $i < $cnt; $i++) {
            $array[$i]['creator'] = '';
            foreach ($users as $user) {
                if ($user['id'] === $array[$i]['creator_id']) {
                    $array[$i]['creator'] = $user['username'];
                }
            }

            $metadata = $wf->getPlaceMetadata($array[$i]['place_at']);
            if (!empty($metadata) && isset($metadata['name'])) {
                $array[$i]['place_at'] = $metadata['name'];
            }

        }
        return $array;
    }

    protected function _getDeptName(array $array = null)
    {
        if (empty($array)) {
            return [];
        }

        $model      = new DeptModel();
        $columnName = ['id', 'name', 'status'];
        $dept       = $model->getDept($columnName);

        $cnt = count($array);
        for ($i = 0; $i < $cnt; $i++) {
            $array[$i]['station'] = '';
            foreach ($dept as $d) {
                if ($d['id'] === $array[$i]['station_id']) {
                    $array[$i]['station'] = $d['name'];
                }
            }
        }
        return $array;
    }

    protected function _getDeptName2(string $id = null)
    {
        if (empty($id)) {
            return '';
        }

        $model      = new DeptModel();
        $columnName = ['id', 'name', 'status'];
        $dept       = $model->getDept($columnName);

        $name = '';
        foreach ($dept as $d) {
            if ($d['id'] === $id) {
                $name = $d['name'];
            }
        }

        return $name;
    }

    protected function _getUserName(array $array = null)
    {
        if (empty($array)) {
            return [];
        }

        $ids    = [];
        $result = [];
        foreach ($array as $key => $value) {
            $ids[]        = $value;
            $result[$key] = '';
        }

        $model      = new UserModel();
        $columnName = ['id', 'username', 'status'];
        $users      = $model->getByIds($columnName, $ids);

        if (empty($users)) {
            return $result;
        }

        foreach ($array as $key => $item) {
            foreach ($users as $user) {
                if ($item === $user['id']) {
                    $result[$key] = $user['username'];
                }
            }
        }
        return $result;
    }

    protected function _getDeviceFullName(string $keys = '')
    {
        if (empty($keys)) {
            return '';
        }

        $array = explode("+", trim($keys, '+'));
        $res   = '';
        if (!empty($array)) {
            $model      = new DeviceModel();
            $columnName = ['id', 'name'];
            $query      = ['ids' => $array];
            $devices    = $model->getByIds($columnName, $query);

            if (!empty($devices)) {
                $text = '';
                foreach ($array as $value) {
                    foreach ($devices as $device) {
                        if ($value === $device['id']) {
                            $text = $text . $device['name'] . ' / ';
                            break;
                        }
                    }
                }
                $res = rtrim($text, ' / ');
            }
        }

        return $res;
    }

    protected function _getStepsBar($creator = '', $reviewer = '', $created_at = '', $updated_at = '', $place = '')
    {
        if (empty($creator) || empty($created_at) || empty($place)) {
            return [
                'current' => 0,
                'step'    => [],
            ];
        }

        $steps = [
            'current' => 0,
            'step'    => [],
        ];

        $wf    = new WfDts();
        $metas = $wf->getPlaceMetaOfName();
        if (empty($metas)) {
            return $steps;
        }

        $temp                     = [];
        $temp[$metas[0]['alias']] = [
            'title'       => $metas[0]['name'],
            'icon'        => false,
            'description' => [$creator, $created_at],
        ];

        $i = 0;
        foreach ($metas as $meta) {
            if ($i === 0) {
                $temp[$meta['alias']] = [
                    'title'       => $meta['name'],
                    'icon'        => false,
                    'description' => [$creator, $created_at],
                ];
            } else {
                $temp[$meta['alias']] = [
                    'title'       => $meta['name'],
                    'icon'        => false,
                    'description' => [],
                ];
            }
            if ($meta['alias'] === $place) {
                $steps['current']             = $i;
                $temp[$meta['alias']]['icon'] = true;
            }
            $i++;
        }

        if ($wf->isReviewPlace($place)) {
            $temp[$place]['description'] = [$reviewer, $updated_at];
        }

        $temp2 = [];
        foreach ($temp as $value) {
            $temp2[] = $value;
        }

        $steps['step'] = $temp2;

        return $steps;
    }
}
