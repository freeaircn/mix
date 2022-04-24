<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-24 23:41:36
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Libraries\Workflow\Dts\WfDts;
use App\Libraries\Workflow\Ticket;
use App\Models\Dts\DeptModel;
use App\Models\Dts\DeviceModel;
use App\Models\Dts\DtsModel;
use App\Models\Dts\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Files\Exceptions\FileException;

class Dts extends BaseController
{
    use ResponseTrait;

    // 模板
    protected $blankTpl;
    protected $updateTpl;
    //
    protected $keyValuePairs;

    public function __construct()
    {
        $this->blankTpl  = "【现象】\n\n【时间】\n\n【影响】\n\n【已采取措施】\n\n";
        $this->updateTpl = "【当前进展】\n\n【下一步计划】\n\n";

        $this->keyValuePairs = [
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

    public function queryEntry()
    {
        $params = $this->request->getGet();
        if (!isset($params['source'])) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        switch ($params['source']) {
            case 'new_form':
                $this->_getNewForm();
                break;
            case 'list':

                break;
            default:
                $res['error'] = '请求数据无效';
                return $this->fail($res);
        }
    }

    public function getDeviceList()
    {
        $params = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('DtsGetDeviceList')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $station_id = $params['station_id'];
        $deviceList = $this->_getDeviceList($station_id);
        if (empty($deviceList)) {
            return $this->failNotFound('请求的数据没有找到');
        }

        $res['data'] = ['deviceList' => $deviceList];
        return $this->respond($res);
    }

    public function uploadAttachment()
    {
        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->failServerError('附件上传失败');
        }

        $config = config('MyGlobalConfig');
        if (!$file->getSize() > $config->dtsAttachmentSize) {
            return $this->fail($config->dtsAttachmentExceedSizeMsg);
        }

        $fileType = $file->getMimeType();
        if ($fileType === 'image/jpeg' || $fileType === 'image/png') {

        } else {
            return $this->fail($config->dtsAttachmentInvalidTypeMsg);
        }

        $orgName = $file->getName();
        $newName = $file->getRandomName();
        // 移动文件
        $path = WRITEPATH . $config->dtsAttachmentPath;
        if (!$file->hasMoved()) {
            try {
                $file->move($path, $newName, true);
            } catch (FileException $exception) {
                return $this->failServerError('保存附件出错');
            }
        }

        $temp = session('dtsAttachment');
        $time = time();
        $data = [
            'id'      => $time,
            'newName' => $newName,
        ];
        if (!$temp) {
            $this->session->set('dtsAttachment', [$data]);
        } else {
            $this->session->push('dtsAttachment', [$data]);
        }

        $res['id'] = $time;
        return $this->respond($res);
    }

    public function delAttachment()
    {
        // 检查请求数据
        if (!$this->validate('DtsDelAttachment')) {
            return $this->respond(['res' => 'invalid']);
        }

        $client = $this->request->getJSON(true);

        $temp = session('dtsAttachment');
        if (empty($temp)) {
            return $this->respond(['res' => 'empty']);
        }

        $config = config('MyGlobalConfig');
        $path   = WRITEPATH . $config->dtsAttachmentPath;
        $new    = [];
        foreach ($temp as $v) {
            if ($client['id'] == $v['id']) {
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $v['newName'];
                if (file_exists($file)) {
                    unlink($file);
                }
            } else {
                $new[] = $v;
            }
        }
        $this->session->remove('dtsAttachment');
        if (!empty($new)) {
            $this->session->set('dtsAttachment', $new);
        }

        return $this->respond(['res' => 'done']);
    }

    public function createOne()
    {
        // 检查请求数据
        if (!$this->validate('DtsCreateOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
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
            return $this->failServerError('处理发生错误，稍候再试');
        }
        $draft['dts_id'] = $dts_id;

        $model  = new DtsModel();
        $result = $model->insertDB($draft);
        if (!$result) {
            return $this->failServerError('处理发生错误，稍候再试');
        }

        // 记录附件

        return $this->respond(['msg' => '创建问题单']);
    }

    public function getQueryParams()
    {
        // $param = $this->request->getGet();

        $allowReadDeptId = session('allowReadDeptId');
        $model           = new DeptModel();
        $columnName      = ['id', 'name'];
        $station         = $model->getByIds($columnName, $allowReadDeptId);

        $wf       = new WfDts();
        $workflow = $wf->getPlaceMetaOfName();

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['station' => $station, 'workflow' => $workflow];
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

        if ($param['station_id'] === '0') {
            $query['station_id'] = session('allowReadDeptId');
        } else {
            $query['station_id'] = [$param['station_id']];
        }

        if ($param['type'] !== '0') {
            $query['type'] = $param['type'];
        }

        if ($param['level'] !== '0') {
            $query['level'] = $param['level'];
        }

        if ($param['dts_id'] !== '') {
            if (preg_match('/^[1-9]\d{0,19}$/', $param['dts_id'])) {
                $query['dts_id'] = $param['dts_id'];
            } else {
                $query['dts_id'] = '0';
            }
        }

        if ($param['creator'] !== '') {
            if (preg_match('/^([\x{4e00}-\x{9fa5}]{1,6})$/u', $param['creator'])) {
                $model      = new UserModel();
                $columnName = ['id'];
                $db         = $model->getByUsername($columnName, $param['creator']);

                if (count($db) === 1) {
                    $query['creator_id'] = $db[0]['id'];
                } else {
                    $res['code'] = EXIT_SUCCESS;
                    $res['data'] = ['total' => 0, 'data' => []];
                    return $this->respond($res);
                }
            } else {
                $res['code'] = EXIT_SUCCESS;
                $res['data'] = ['total' => 0, 'data' => []];
                return $this->respond($res);
            }
        }

        if ($param['place_at'] !== 'all') {
            $wf       = new WfDts();
            $workflow = $wf->getPlaceAlias();
            if (in_array($param['place_at'], $workflow)) {
                $query['place_at'] = $param['place_at'];
            } else {
                $res['code'] = EXIT_SUCCESS;
                $res['data'] = ['total' => 0, 'data' => []];
                return $this->respond($res);
            }
        }

        $query['status'] = 'publish';
        $query['limit']  = $param['limit'];
        $query['offset'] = $param['offset'];
        $columnName      = ['id', 'dts_id', 'station_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator_id'];

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

        // id -> name
        $details['type']  = $this->keyValuePairs['type'][$db['type']];
        $details['level'] = $this->keyValuePairs['level'][$db['level']];

        $users_id = [
            'creator'  => $db['creator_id'],
            'reviewer' => $db['reviewer_id'],
        ];
        $users               = $this->_getUserName($users_id);
        $details['creator']  = $users['creator'];
        $details['reviewer'] = $users['reviewer'];

        $details['device']  = $this->_getDeviceFullName($db['device']);
        $details['station'] = $this->_getDeptName2($db['station_id']);

        $steps     = $this->_getStepsBar($details['creator'], $details['reviewer'], $db['created_at'], $db['updated_at'], $db['place_at']);
        $operation = $this->_getWorkflowOperation($db['place_at']);

        //
        $res['code'] = EXIT_SUCCESS;
        $res['data'] = [
            'details'        => $details,
            'steps'          => $steps,
            'newProgressTpl' => $newProgressTpl,
            'operation'      => $operation,
        ];

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

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '没有权限修改';
        }

        $progress = $this->_getContentTplHead() . $client['progress'];

        $result = $model->updateProgress($progress, $dts_id);
        if ($result !== false) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '进展已更新';
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
    // public function getBlankForm()
    protected function _getNewForm()
    {
        $allowWriteDeptId = session('allowWriteDeptId');
        if (empty($allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $station_id = session('allowDefaultDeptId');
        if (empty($station_id)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $deviceList = $this->_getDeviceList($station_id);
        if (empty($deviceList)) {
            return $this->failNotFound('请求的数据没有找到');
        }

        $model       = new DeptModel();
        $columnName  = ['id', 'name'];
        $station     = $model->getByIds($columnName, $allowWriteDeptId);
        $description = $this->blankTpl;

        $res['data'] = [
            'description' => $description,
            'deviceList'  => $deviceList,
            'station'     => $station,
        ];
        var_dump('here');
        return $this->respond($res);
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

    public function _getWorkflowOperation(string $place_at = '')
    {
        $result = [
            'allowUpdateProgress' => false,
            'allowAppraise'       => false,
            'allowResolve'        => false,
            'allowClose'          => false,
        ];

        if (empty($place_at)) {
            return $result;
        }

        $wf                            = new WfDts();
        $result['allowUpdateProgress'] = $wf->canUpdateProgress($place_at);

        return $result;
    }
}
