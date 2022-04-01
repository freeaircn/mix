<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-01 23:17:36
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Libraries\Workflow\Dts\WfDts;
use App\Libraries\Workflow\Ticket;
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
        $params = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('DtsGetBlankForm')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $station_id  = $params['station_id'];
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
        if (!$this->validate('DtsDraftPost')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);
        //
        $uid   = session('id');
        $draft = [
            'station_id'  => $client['station_id'],
            'type'        => $client['type'],
            'title'       => $client['title'],
            'level'       => $client['level'],
            'device'      => $client['device'],
            'description' => $this->_getContentTplHead() . $client['description'],
            'creator_id'  => $uid,
        ];

        // 工作流
        $wf_config         = rtrim(APPPATH, '\\/ ') . DIRECTORY_SEPARATOR . config('MyGlobalConfig')->wfDtsConfigFile;
        $wf                = new WfDts($wf_config);
        $draft['place_at'] = $wf->getFirstPlace();

        // dts_id
        $maker  = new MyIdMaker();
        $dts_id = $maker->apply('DTS');
        if ($dts_id === false) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '失败，稍后再试';

            return $this->respond($res);
        }
        $draft['dts_id'] = $dts_id;
        // $draft['dts_id'] = '1';

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
        $ticket['type']  = $this->keyValueMap['type'][$db['type']];
        $ticket['level'] = $this->keyValueMap['level'][$db['level']];

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
        $progress = $this->_getContentTplHead() . $client['progress'];

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

    protected function _getContentTplHead()
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
