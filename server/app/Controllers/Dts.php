<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-29 15:24:29
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Libraries\Workflow\Dts\WfDts;
use App\Libraries\Workflow\Ticket;
use App\Models\Dts\DeptModel;
use App\Models\Dts\DeviceModel;
use App\Models\Dts\DtsAttachmentModel;
use App\Models\Dts\DtsModel;
use App\Models\Dts\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Files\Exceptions\FileException;

class Dts extends BaseController
{
    use ResponseTrait;

    // 模板
    protected $progressTemplates;
    //
    protected $keyValuePairs;

    public function __construct()
    {
        $config                  = config('MyGlobalConfig');
        $this->progressTemplates = $config->dtsProgressTemplates;
        $this->keyValuePairs     = $config->dtsKeyValuePairs;
    }

    public function queryEntry()
    {
        $resource = $this->request->getGet('resource');
        if (empty($resource)) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        switch ($resource) {
            case 'search_params':
                $result = $this->_reqSearchParams();
                break;
            case 'list':
                $result = $this->_reqList();
                break;
            case 'details':
                $result = $this->_reqDetails();
                break;
            case 'new_form':
                $result = $this->_reqNewForm();
                break;
            case 'device_list':
                $result = $this->_reqDeviceList();
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
            $response['data'] = $result['data'];
            return $this->respond($response);
        }

        return $this->failServerError('服务器内部错误');
    }

    public function uploadAttachment()
    {
        if (!$this->validate('DtsUploadAttachment')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $dts_id = $this->request->getPost('dts_id');
        $file   = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->failServerError('附件上传失败');
        }

        $config = config('MyGlobalConfig');
        if (!$file->getSize() > $config->dtsAttachmentSize) {
            return $this->fail($config->dtsAttachmentExceedSizeMsg);
        }
        if (!$file->getSize() === 0) {
            return $this->fail('上传空文件');
        }

        $fileType = $file->getMimeType();
        if (!in_array($fileType, $config->dtsAttachmentFileTypes)) {
            return $this->fail($config->dtsAttachmentInvalidTypeMsg);
        }

        $orgName = $file->getName();
        $size    = $file->getSize();
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

        if ($dts_id === '0') {
            $temp = session('dtsAttachment');
            $time = time();
            $data = [
                'id'      => $time,
                'orgName' => $orgName,
                'newName' => $newName,
                'size'    => $size,
            ];
            if (!$temp) {
                $this->session->set('dtsAttachment', [$data]);
            } else {
                $this->session->push('dtsAttachment', [$data]);
            }

            $res['id'] = $time;
            return $this->respond($res);
        }
        //
        if ($dts_id !== '0') {
            $ext        = explode('.', $orgName);
            $attachment = [
                'dts_id'   => $dts_id,
                'user_id'  => session('id'),
                'username' => session('username'),
                'org_name' => $orgName,
                'new_name' => $newName,
                'size'     => $size,
                'file_ext' => end($ext),
            ];
            $model = new DtsAttachmentModel();
            $id    = $model->insertSingleRecord($attachment);
            if ($id === false) {
                // 删除移动后的文件
                $config = config('MyGlobalConfig');
                $path   = WRITEPATH . $config->dtsAttachmentPath;
                $file   = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $newName;
                if (file_exists($file)) {
                    unlink($file);
                }
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $res['id'] = $id;
            return $this->respond($res);
        }

    }

    public function delAttachment()
    {
        if (!$this->validate('DtsDelAttachment')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $client['id'];
        $dts_id = $client['dts_id'];

        if ($dts_id === '0') {
            $temp = session('dtsAttachment');
            if (empty($temp)) {
                return $this->respond(['res' => 'empty']);
            }

            $config = config('MyGlobalConfig');
            $path   = WRITEPATH . $config->dtsAttachmentPath;
            $new    = [];
            foreach ($temp as $v) {
                if ($id == $v['id']) {
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

            return $this->respond([
                'res' => 'done',
            ]);
        }

        if ($dts_id !== '0') {
            $columnName = ['dts_id', 'new_name'];

            $model = new DtsAttachmentModel();
            $db    = $model->getById($columnName, $id);
            if (empty($db)) {
                return $this->respond(['res' => 'done']);
            }
            if ($db['dts_id'] !== $dts_id) {
                return $this->failServerError('请求错误，刷新后再试');
            }
            $result = $model->delById($id);
            if (!$result) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
            $config = config('MyGlobalConfig');
            $path   = WRITEPATH . $config->dtsAttachmentPath;
            $file   = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['new_name'];
            if (file_exists($file)) {
                unlink($file);
            }

            return $this->respond(['res' => 'done']);
        }
    }

    public function downloadAttachment()
    {
        if (!$this->validate('DtsDownloadAttachment')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $params = $this->request->getGet();
        $id     = $params['id'];
        $dts_id = $params['dts_id'];

        $columnName = ['dts_id', 'org_name', 'new_name', 'size'];
        $model      = new DtsAttachmentModel();
        $db         = $model->getById($columnName, $id);
        if (empty($db)) {
            return $this->failNotFound('附件不存在');
        }

        if ($db['dts_id'] !== $dts_id) {
            return $this->failNotFound('附件不存在');
        }

        $columnName = ['station_id'];
        $model      = new DtsModel();
        $db2        = $model->getByDtsId($columnName, $dts_id);
        if (empty($db2)) {
            return $this->failNotFound('附件不存在');
        }
        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($db2['station_id'], $allowReadDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $config = config('MyGlobalConfig');
        $path   = WRITEPATH . $config->dtsAttachmentPath;
        $file   = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['new_name'];
        if (!file_exists($file)) {
            return $this->failNotFound('附件不存在');
        }

        $type = filetype($file);
        header("Content-type:" . $type);
        header("Content-Disposition: attachment; filename=" . urlencode($db['org_name']));
        header("Content-Transfer-Encoding: binary");
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file);
        exit;
    }

    public function createOne()
    {
        if (!$this->validate('DtsCreateOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($client['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }
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
            $res['info']  = 'id maker failed';
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res, 500);
        }
        $draft['dts_id'] = $dts_id;

        $model  = new DtsModel();
        $result = $model->insertSingleRecord($draft);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 附件
        $files        = $client['files'];
        $files_stored = session('dtsAttachment');
        $temp         = [];
        if (!empty($files) && !empty($files_stored)) {
            foreach ($files as $c) {
                foreach ($files_stored as $s) {
                    if ($c['id'] == $s['id']) {
                        $temp[] = $s;
                    }
                }
            }
        }
        if (!empty($temp)) {
            $attachments = [];
            foreach ($temp as $t) {
                $ext           = explode('.', $t['orgName']);
                $attachments[] = [
                    'dts_id'   => $dts_id,
                    'user_id'  => session('id'),
                    'username' => session('username'),
                    'org_name' => $t['orgName'],
                    'new_name' => $t['newName'],
                    'size'     => $t['size'],
                    'file_ext' => end($ext),
                ];
            }
            $model  = new DtsAttachmentModel();
            $result = $model->insertMultiRecords($attachments);
            if ($result === false) {
                // 回退上一步操作
                $model  = new DtsModel();
                $result = $model->delByDtsId($dts_id);
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
        }

        if ($this->session->has('dtsAttachment')) {
            $this->session->remove('dtsAttachment');
        }

        return $this->respond([
            'msg' => '创建问题单',
        ]);
    }

    public function deleteOne()
    {
        if (!$this->validate('DtsDeleteOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

        $columnName = ['station_id', 'place_at'];
        $dts_id     = $client['dts_id'];

        $model = new DtsModel();
        $db    = $model->getByDtsId($columnName, $dts_id);
        if (empty($db)) {
            return $this->respond(['info' => 'empty']);
        }

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $wf         = new WfDts();
        $firstPlace = $wf->getFirstPlace();
        if ($db['place_at'] !== $firstPlace) {
            return $this->failUnauthorized('用户没有权限，请联系管理员删除');
        }

        // 附件
        $columnName = ['new_name'];
        $model      = new DtsAttachmentModel();
        $files      = $model->getByDtsId($columnName, $dts_id);
        if (!empty($files)) {
            $config = config('MyGlobalConfig');
            $path   = WRITEPATH . $config->dtsAttachmentPath;
            foreach ($files as $v) {
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $v['new_name'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $result = $model->delByDtsId($dts_id);
        }

        //
        $model  = new DtsModel();
        $result = $model->delByDtsId($dts_id);
        if (!$result) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        return $this->respond([
            'msg' => '删除问题单',
        ]);
    }

    public function updateEntry()
    {
        if (!$this->validate('DtsUpdateEntry')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client   = $this->request->getJSON(true);
        $resource = $client['resource'];

        $resources = ['progress', 'score', 'to_resolve', 'back_work', 'to_close'];
        if (!in_array($resource, $resources)) {
            return $this->fail('请求数据无效');
        }

        if ($resource === 'progress') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->respond(['msg' => '空白的新进展']);
            }

            $model      = new DtsModel();
            $columnName = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db         = $model->getByDtsId($columnName, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = session('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = ['progress' => $text];

            $wf = new WfDts($db['place_at']);
            if ($wf->toWorking()) {
                $data['place_at'] = $wf->getTicket()->getCurrentPlace();
            }

            $result = $model->updateById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            } else {
                return $this->respond(['msg' => '已更新进展']);
            }
        }

        if ($resource === 'score') {
            $dts_id = $client['dts_id'];

            $model      = new DtsModel();
            $columnName = ['id', 'dts_id', 'station_id', 'place_at'];
            $db         = $model->getByDtsId($columnName, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = session('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $data = [
                'score'      => $client['score'],
                'score_desc' => $client['score_desc'],
                'scored_by'  => $this->_getContentHeadTpl(),
            ];

            $result = $model->updateById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            } else {
                return $this->respond(['msg' => '已评分']);
            }
        }

        if ($resource === 'to_resolve') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->fail('解决意见不能空白');
            }

            $model      = new DtsModel();
            $columnName = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db         = $model->getByDtsId($columnName, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->canResolve()) {
                return $this->fail('流程不允许转到 - 解决');
            }

            $allowWriteDeptId = session('allowWriteDeptId');
            $allowWorkflow    = session('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_resolve', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = ['progress' => $text];

            $wf = new WfDts($db['place_at']);
            if (!$wf->toResolve()) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            } else {
                return $this->respond(['msg' => '已提交解决']);
            }
        }

        if ($resource === 'back_work') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->fail('重新处理意见不能空白');
            }

            $model      = new DtsModel();
            $columnName = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db         = $model->getByDtsId($columnName, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->canBackWorking()) {
                return $this->fail('流程不允许转到 - 处理中');
            }

            $allowWriteDeptId = session('allowWriteDeptId');
            $allowWorkflow    = session('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_back_work', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = ['progress' => $text];

            $wf = new WfDts($db['place_at']);
            if (!$wf->toBackWorking()) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            } else {
                return $this->respond(['msg' => '已提交重新处理']);
            }
        }

        if ($resource === 'to_close') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->fail('关闭审核意见不能空白');
            }

            $model      = new DtsModel();
            $columnName = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db         = $model->getByDtsId($columnName, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->canClose()) {
                return $this->fail('流程不允许转到 - 关闭');
            }

            $allowWriteDeptId = session('allowWriteDeptId');
            $allowWorkflow    = session('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_close', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = ['progress' => $text];

            $wf = new WfDts($db['place_at']);
            if (!$wf->toClose()) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            } else {
                return $this->respond(['msg' => '已提交关闭']);
            }
        }

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

    // Part-2
    protected function _reqSearchParams()
    {
        $allowReadDeptId = session('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['station' => [], 'workflow' => []];
            return $res;
        }

        $model      = new DeptModel();
        $columnName = ['id', 'name'];
        $station    = $model->getByIds($columnName, $allowReadDeptId);

        $wf       = new WfDts();
        $workflow = $wf->getPlaceMetaOfName();

        $res['http_status'] = 200;
        $res['data']        = ['station' => $station, 'workflow' => $workflow];
        return $res;
    }

    protected function _reqList()
    {
        if (!$this->validate('DtsReqList')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $param = $this->request->getGet();

        $allowReadDeptId = session('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }

        if ($param['station_id'] === '0') {
            $query['station_id'] = $allowReadDeptId;
        } else {
            if (!in_array($param['station_id'], $allowReadDeptId)) {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'data' => []];
                return $res;
            } else {
                $query['station_id'] = [$param['station_id']];
            }
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
                    $res['http_status'] = 200;
                    $res['data']        = ['total' => 0, 'data' => []];
                    return $res;
                }
            } else {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'data' => []];
                return $res;
            }
        }

        if ($param['place_at'] !== 'all') {
            $wf       = new WfDts();
            $workflow = $wf->getPlaceAlias();
            if (in_array($param['place_at'], $workflow)) {
                $query['place_at'] = $param['place_at'];
            } else {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'data' => []];
                return $res;
            }
        }

        $query['status'] = 'publish';
        $query['limit']  = $param['limit'];
        $query['offset'] = $param['offset'];
        $columnName      = ['id', 'dts_id', 'station_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator_id'];

        $model = new DtsModel();
        $db    = $model->getByLimitOffset($columnName, $query);

        if ($db['total'] === 0) {
            $res['http_status'] = 200;
            $res['data']        = $db;
            return $res;
        }

        // id => name
        $data = $this->_getUserNameAndWorkflowName($db['data']);
        $data = $this->_getDeptName($data);

        $res['http_status'] = 200;
        $res['data']        = ['total' => $db['total'], 'data' => $data];
        return $res;
    }

    protected function _reqNewForm()
    {
        $allowWriteDeptId = session('allowWriteDeptId');
        if (empty($allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $station_id = session('allowDefaultDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $deviceList = $this->_getDeviceList($station_id);
        if (empty($deviceList)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $model       = new DeptModel();
        $columnName  = ['id', 'name'];
        $station     = $model->getByIds($columnName, $allowWriteDeptId);
        $description = $this->progressTemplates['new_form'];

        $res['http_status'] = 200;
        $res['data']        = [
            'description' => $description,
            'deviceList'  => $deviceList,
            'station'     => $station,
        ];
        return $res;
    }

    protected function _reqDeviceList()
    {
        if (!$this->validate('DtsReqDeviceList')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }
        $params     = $this->request->getGet();
        $station_id = $params['station_id'];

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $deviceList = $this->_getDeviceList($station_id);
        if (empty($deviceList)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $res['http_status'] = 200;
        $res['data']        = ['deviceList' => $deviceList];
        return $res;
    }

    protected function _reqDetails()
    {
        // 检查请求数据
        if (!$this->validate('DtsReqDetails')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params = $this->request->getGet();
        $dts_id = $params['dts_id'];

        $columnName = ['dts_id', 'type', 'title', 'level', 'station_id', 'place_at', 'device', 'description', 'progress', 'created_at', 'updated_at', 'creator_id', 'reviewer_id', 'score', 'score_desc', 'scored_by'];
        $model      = new DtsModel();
        $db         = $model->getByDtsId($columnName, $dts_id);
        if (empty($db)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($db['station_id'], $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }
        $details = $db;

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

        $steps       = $this->_getStepsBar($details['creator'], $details['reviewer'], $db['created_at'], $db['updated_at'], $db['place_at']);
        $operation   = $this->_getWorkflowOperation($db['place_at']);
        $attachments = $this->_getAttachment($dts_id);

        $res['http_status'] = 200;
        $res['data']        = [
            'details'           => $details,
            'steps'             => $steps,
            'progressTemplates' => $this->progressTemplates,
            'operation'         => $operation,
            'attachments'       => $attachments,
        ];
        return $res;
    }

    // Part-3
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

    protected function _getContentHeadTpl()
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
        $metas = $wf->getPlaceLine();
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

        $temp2 = [];
        foreach ($temp as $value) {
            $temp2[] = $value;
        }

        $steps['step'] = $temp2;

        return $steps;
    }

    protected function _getWorkflowOperation(string $place_at = '')
    {
        $result = [
            'allowUpdateProgress'   => false,
            'allowScore'            => false,
            'allowResolve'          => false,
            'allowClose'            => false,
            'allowBackWork'         => false,
            'showRmvAttachmentIcon' => false,
        ];

        $wf  = new WfDts($place_at);
        $ops = $wf->getPlaceAllowOp();

        $result['allowUpdateProgress']   = isset($ops['updateProgress']) ? $ops['updateProgress'] : false;
        $result['showRmvAttachmentIcon'] = isset($ops['showRmvAttachmentIcon']) ? $ops['showRmvAttachmentIcon'] : false;

        if ($this->session->has('allowWorkflow')) {
            $allowWorkflow        = session('allowWorkflow');
            $result['allowScore'] = in_array('dts_score', $allowWorkflow) ? true : false;

            if ($wf->canResolve()) {
                $result['allowResolve'] = in_array('dts_resolve', $allowWorkflow) ? true : false;
            }

            if ($wf->canClose()) {
                $result['allowClose'] = in_array('dts_close', $allowWorkflow) ? true : false;
            }

            if ($wf->canWorking()) {
                $result['allowBackWork'] = in_array('dts_back_work', $allowWorkflow) ? true : false;
            }
        }

        return $result;
    }

    protected function _getAttachment(string $dts_id = null)
    {
        if (!is_numeric($dts_id)) {
            return [];
        }

        $columnName = ['id', 'org_name'];
        $model      = new DtsAttachmentModel();
        $files      = $model->getByDtsId($columnName, $dts_id);

        $attachments = [];
        foreach ($files as $f) {
            $attachments[] = [
                'uid'      => $f['id'],
                'name'     => $f['org_name'],
                'status'   => 'done',
                'response' => ['id' => $f['id']],
            ];
        }

        return $attachments;
    }
}
