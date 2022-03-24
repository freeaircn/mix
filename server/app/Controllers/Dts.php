<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-03-24 20:51:39
 */

namespace App\Controllers;

use App\Models\Dts\DeviceModel;
use App\Models\Dts\RoleWorkflowAuthorityModel;
use App\Models\Dts\TicketModel;
use App\Models\Dts\UserModel;
use App\Models\Dts\UserRoleModel;
use App\Models\Dts\WorkflowAuthModel;
use App\MyEntity\Workflow\Dts\Ticket;
use App\MyEntity\Workflow\Dts\WorkflowCore;
use CodeIgniter\API\ResponseTrait;

class Dts extends BaseController
{
    use ResponseTrait;

    // 进展模板
    protected $progressTemplate;
    // 单号末尾几位
    protected $ticketIdTailLength;
    //
    protected $ticketTextMap;

    public function __construct()
    {
        $this->progressTemplate = [
            'draft'  => "【现象描述】\n\n【发生时间】\n\n【影响】\n\n【已采取措施】\n\n",
            'update' => "【当前进展】\n\n【下一步计划】\n\n",
        ];

        $this->ticketIdTailLength = 3;

        $this->ticketTextMap = [
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

    public function getTicketBlankForm()
    {
        $params = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('DtsGetTicketBlankForm')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $station_id = $params['station_id'];

        //
        $progress = $this->getProgressTemplate();

        //
        $wf         = new WorkflowCore();
        $firstPlace = $wf->getFirstPlace();
        // $nextPlace  = $firstPlace;
        // do {
        //     $nextPlace = $wf->getNextPlace($nextPlace);
        // } while (!$wf->isHandlingPlace($nextPlace));

        $nextPlace = $wf->getNextPlace($firstPlace);
        $handler   = [];
        if ($wf->isHandlingPlace($nextPlace)) {
            $handler = $this->getHandler($station_id, $nextPlace);
        }

        //
        $model      = new DeviceModel();
        $columnName = ['id', 'pid', 'name'];
        $query      = ['id >' => 1];
        $deviceList = $model->get($columnName, $query);

        if (empty($progress) || empty($handler) || empty($deviceList)) {
            $res['code'] = EXIT_ERROR;
        } else {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = [
                'progress'   => $progress,
                'handler'    => $handler,
                'deviceList' => $deviceList,
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
        if (!$this->validate('DtsDraftPost')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);
        $model  = new TicketModel();

        // 取出数据
        $uid   = session('id');
        $draft = [
            'station_id'     => $client['station_id'],
            'type'           => $client['type'],
            'title'          => $client['title'],
            'level'          => $client['level'],
            'equipment_unit' => $client['equipment_unit'],
            'progress'       => $this->getProgressHead() . $client['progress'],
            'handler'        => $client['handler'],
            'creator'        => $uid,
        ];

        // 工作流
        $wf                = new WorkflowCore();
        $firstPlace        = $wf->getFirstPlace();
        $draft['place_at'] = $wf->getNextPlace($firstPlace);

        $result = $model->newDraft($draft, $this->ticketIdTailLength);
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
        if (!$this->validate('DtsTicketsGet')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $model = new TicketModel();

        $query['station_id'] = $param['station_id'];
        $query['limit']      = $param['limit'];
        $query['offset']     = $param['offset'];

        $columnName = ['id', 'dts_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator', 'handler'];
        $db         = $model->getByLimitOffset($columnName, $query);

        if ($db['total'] === 0) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['total' => 0, 'data' => []];
            return $this->respond($res);
        }

        // uid => name
        $model      = new UserModel();
        $columnName = ['id', 'username', 'status'];
        $station    = '+' . $query['station_id'] . '+';
        $users      = $model->getUserByStation($columnName, $station);
        $cnt        = count($users);

        // 过滤
        $wf = new WorkflowCore();
        for ($i = 0; $i < $db['total']; $i++) {
            // 工作流
            $metadata = $wf->getPlaceMetadata($db['data'][$i]['place_at']);
            if (!empty($metadata)) {
                $db['data'][$i]['place_at'] = $metadata['name'];
            }

            // 用户名
            for ($j = 0; $j < $cnt; $j++) {
                if ($users[$j]['id'] === $db['data'][$i]['creator']) {
                    $db['data'][$i]['creator'] = $users[$j]['username'];
                }
                if ($users[$j]['id'] === $db['data'][$i]['handler']) {
                    $db['data'][$i]['handler'] = $users[$j]['username'];
                }
            }
        }

        if ($db === false) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '稍后再试';
        } else {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['total' => $db['total'], 'data' => $db['data']];
        }

        return $this->respond($res);
    }

    public function getTicketDetails()
    {
        // 检查请求数据
        if (!$this->validate('DtsGetTicketDetails')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $model = new TicketModel();

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $ticket_id  = $params['ticket_id'];

        $columnName = ['ticket_id', 'type', 'title', 'level', 'place_at', 'equipment_unit', 'progress', 'created_at', 'updated_at', 'creator', 'handler', 'reviewer'];
        $query      = [
            'ticket_id' => $ticket_id,
        ];
        $db = $model->getByTicketId($columnName, $query);
        if (empty($db)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '问题单不存在';
            return $this->respond($res);
        }

        $ticket = $db;
        // id => 名称
        $ticket['type']  = $this->ticketTextMap['type'][$db['type']];
        $ticket['level'] = $this->ticketTextMap['level'][$db['level']];

        //
        $place      = $db['place_at'];
        $creator    = $db['creator'];
        $handler    = $db['handler'];
        $reviewer   = $db['reviewer'];
        $created_at = $db['created_at'];
        $updated_at = $db['updated_at'];

        // uid => 用户名
        $ticket['creator']  = '-';
        $ticket['handler']  = '-';
        $ticket['reviewer'] = '-';

        $model      = new UserModel();
        $columnName = ['id', 'username', 'status'];
        $users      = $model->getUserWhereId($columnName, [$creator, $handler, $reviewer]);
        foreach ($users as $value) {
            if ($value['id'] === $creator) {
                $ticket['creator'] = $value['username'];
            }
            if ($value['id'] === $handler) {
                $ticket['handler'] = $value['username'];
            }
            if ($value['id'] === $reviewer) {
                $ticket['reviewer'] = $value['username'];
            }
        }

        //
        $progressText = $this->getProgressTemplate('update');

        // 所属设备
        $ticket['equipment_unit'] = $this->equipmentUnitTextMap($db['equipment_unit']);

        // 进度
        $wf                 = new WorkflowCore();
        $meta               = $wf->getPlaceMetadata($place);
        $ticket['place_at'] = $meta['name'];
        $wfPlaceAuth        = $meta['auth'];

        $steps = $this->StepsTextMap($ticket['creator'], $ticket['handler'], $ticket['reviewer'], $created_at, $updated_at, $place);

        // 用户权限
        $view = [
            'isCreator' => false,
            'isCheck'   => false,
            'isReview'  => false,
        ];
        $handlers  = [];
        $reviewers = [];

        $uid            = session('id');
        $ownWfAuthority = session('wfAuthority');

        if ($wf->isCheckPlace($place)) {
            if ($handler === $uid || in_array($wfPlaceAuth, $ownWfAuthority)) {
                $view = $wf->getPlaceMetaOfView($place);
                $temp = $this->getHandler($station_id, $place);
                foreach ($temp as $value) {
                    if ($value['id'] !== $handler) {
                        $handlers[] = $value;
                    }
                }

                $temp = $this->getHandler($station_id, $wf->getReviewPlace());
                foreach ($temp as $value) {
                    if ($value['id'] !== $handler) {
                        $reviewers[] = $value;
                    }
                }
            }
        }

        //
        $res['code'] = EXIT_SUCCESS;
        $res['data'] = [
            'ticket'       => $ticket,
            'steps'        => $steps,
            'progressText' => $progressText,
            'view'         => $view,
            'handlers'     => $handlers,
            'reviewers'    => $reviewers,
        ];
        // $res['extra'] = $view;

        return $this->respond($res);
    }

    public function putTicketProgress()
    {
        // 检查请求数据
        if (!$this->validate('DtsProgressPut')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);
        $where  = [
            'ticket_id' => $client['ticket_id'],
        ];
        $progress = $this->getProgressHead() . $client['progress'];

        $model  = new TicketModel();
        $result = $model->updateProgress($progress, $where);
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

    protected function getProgressHead()
    {
        return date('Y-m-d H:i', time()) . ' ' . session('username') . "\n";
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

    protected function equipmentUnitTextMap($data = '')
    {
        if (empty($data)) {
            return '';
        }

        $temp = explode("+", trim($data, '+'));
        $res  = '';
        if (count($temp) > 0) {
            $model      = new DeviceModel();
            $columnName = ['id', 'name'];
            $query      = [
                'ids' => $temp,
            ];
            $db2 = $model->getByIds($columnName, $query);
            if (!empty($db2)) {
                $cnt  = count($temp);
                $text = '';
                for ($i = 0; $i < $cnt; $i++) {
                    for ($j = 0; $j < count($db2); $j++) {
                        if ($temp[$i] === $db2[$j]['id']) {
                            $text = $text . $db2[$j]['name'] . ' / ';
                            break;
                        }
                    }
                }
                $res = rtrim($text, ' / ');
            }
        }

        return $res;
    }

    protected function StepsTextMap($creator = '', $handler = '', $reviewer = '', $created_at = '', $updated_at = '', $place = '')
    {
        $steps = [
            'current' => '0',
            'step'    => [],
        ];

        if (empty($created_at) || empty($updated_at) || empty($place)) {
            return $steps;
        }

        $wf   = new WorkflowCore();
        $meta = $wf->getPlaceMetaOfName();
        $cnt  = count($meta);

        $temp                    = [];
        $temp[$meta[0]['alias']] = [
            'title'       => $meta[0]['name'],
            'icon'        => false,
            'description' => [$creator, $created_at],
        ];
        for ($i = 1; $i < $cnt; $i++) {
            if ($meta[$i]['alias'] === $place) {
                $steps['current'] = $i;
            }
            $temp[$meta[$i]['alias']] = [
                'title'       => $meta[$i]['name'],
                'icon'        => false,
                'description' => [],
            ];
        }

        if ($wf->isCheckPlace($place)) {
            $temp[$place]['icon']        = true;
            $temp[$place]['description'] = [$handler, $updated_at];
        }

        if ($wf->isReviewPlace($place)) {
            $temp[$place]['icon']        = true;
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
