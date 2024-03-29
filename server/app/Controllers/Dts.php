<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-05-17 11:05:37
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Libraries\Workflow\Dts\WfDts;
use App\Models\Common\DeptModel;
use App\Models\Common\DeviceModel;
use App\Models\Common\UserModel;
// use App\Models\Dts\DtsAttachmentModel;
use App\Models\Dts\Attachment as DtsAttachmentModel;
use App\Models\Dts\DtsModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\CriticalError;
use CodeIgniter\Files\Exceptions\FileException;

class Dts extends BaseController
{
    use ResponseTrait;

    protected $selfConfig;

    public function __construct()
    {
        $this->selfConfig = config('Config\\MyConfig\\Dts');
    }

    public function queryEntry()
    {
        $resource = $this->request->getGet('resource');
        if (empty($resource)) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        switch ($resource) {
            case 'news':
                $result = $this->_reqNews();
                break;
            case 'search_params':
                $result = $this->_reqSearchParams();
                break;
            case 'list':
                $result = $this->_reqList();
                break;
            case 'details':
                $result = $this->_reqDetails();
                break;
            case 'pre_edit':
                $result = $this->_preEdit();
                break;
            case 'new_form':
                $result = $this->_reqNewForm();
                break;
            case 'device_list':
                $result = $this->_reqDeviceList();
                break;
            case 'statistic_chart':
                $result = $this->_reqStatisticChartData();
                break;
            case 'attachments_list':
                $result = $this->_reqAttachmentsList();
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

    public function uploadAttachment()
    {
        if (!$this->validate('DtsUploadAttachment')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $dts_id     = $this->request->getPost('dts_id');
        $station_id = $this->request->getPost('station_id');
        $file       = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->failServerError('附件上传失败');
        }

        if (!$file->getSize() > $this->selfConfig->attachmentSize) {
            return $this->fail($this->selfConfig->attachmentExceedSizeMsg);
        }
        if (!$file->getSize() === 0) {
            return $this->fail('上传空文件');
        }

        $fileType = $file->getMimeType();
        if (!in_array($fileType, $this->selfConfig->attachmentFileTypes)) {
            return $this->fail($this->selfConfig->attachmentInvalidTypeMsg);
        }

        $orgName = $file->getName();
        $size    = $file->getSize();
        $newName = $file->getRandomName();
        // 移动文件
        $path = WRITEPATH . $this->selfConfig->attachmentPath;
        if (!$file->hasMoved()) {
            try {
                $file->move($path, $newName, true);
            } catch (FileException $exception) {
                return $this->failServerError('保存附件出错');
            }
        }

        if ($dts_id === '0') {
            $temp = $this->session->get('dtsAttachment');
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
                'station_id' => $station_id,
                'dts_id'     => $dts_id,
                'user_id'    => $this->session->get('id'),
                'username'   => $this->session->get('username'),
                'org_name'   => $orgName,
                'new_name'   => $newName,
                'size'       => $size,
                'file_ext'   => end($ext),
            ];
            $model = new DtsAttachmentModel();
            $id    = $model->createDtsAttachmentSingleRecord($attachment);
            if ($id === false) {
                // 删除移动后的文件
                $path = WRITEPATH . $this->selfConfig->attachmentPath;
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $newName;
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
            $temp = $this->session->get('dtsAttachment');
            if (empty($temp)) {
                return $this->respond(['res' => 'empty']);
            }

            $path = WRITEPATH . $this->selfConfig->attachmentPath;
            $new  = [];
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

            return $this->respond(['res' => 'done']);
        }

        if ($dts_id !== '0') {
            $fields = ['dts_id', 'new_name'];

            $model = new DtsAttachmentModel();
            $db    = $model->getDtsAttachmentRecordById($fields, $id);
            if (empty($db)) {
                return $this->respond(['res' => 'done']);
            }
            if ($db['dts_id'] !== $dts_id) {
                return $this->failServerError('请求错误，刷新页面后再试');
            }
            $result = $model->delDtsAttachmentRecordById($id);
            if (!$result) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
            $path = WRITEPATH . $this->selfConfig->attachmentPath;
            $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['new_name'];
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

        $fields = ['dts_id', 'org_name', 'new_name', 'size'];
        $model  = new DtsAttachmentModel();
        $db     = $model->getDtsAttachmentRecordById($fields, $id);
        if (empty($db)) {
            return $this->failNotFound('附件不存在');
        }

        if ($db['dts_id'] !== $dts_id) {
            return $this->failNotFound('附件不存在');
        }

        $fields = ['station_id'];
        $model  = new DtsModel();
        $db2    = $model->getDtsRecordByDtsId($fields, $dts_id);
        if (empty($db2)) {
            return $this->failNotFound('附件不存在');
        }
        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($db2['station_id'], $allowReadDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $path = WRITEPATH . $this->selfConfig->attachmentPath;
        $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['new_name'];
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

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($client['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }
        //
        $uid   = $this->session->get('id');
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
        $draft['place_at'] = $wf->getStartPlace('main');

        $draft['status'] = 'publish';
        // dts_id
        $maker  = new MyIdMaker();
        $dts_id = $maker->apply('UUID');
        if ($dts_id === false) {
            $res['info']  = 'id maker failed';
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res, 500);
        }
        $draft['dts_id'] = $dts_id;

        $model  = new DtsModel();
        $result = $model->createSingleDtsRecord($draft);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 附件
        $files        = $client['files'];
        $files_stored = $this->session->get('dtsAttachment');
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
                    'station_id' => $client['station_id'],
                    'dts_id'     => $dts_id,
                    'user_id'    => $this->session->get('id'),
                    'username'   => $this->session->get('username'),
                    'org_name'   => $t['orgName'],
                    'new_name'   => $t['newName'],
                    'size'       => $t['size'],
                    'file_ext'   => end($ext),
                ];
            }
            $model  = new DtsAttachmentModel();
            $result = $model->createDtsAttachmentMultiRecords($attachments);
            if ($result === false) {
                // 回退上一步操作
                $model  = new DtsModel();
                $result = $model->delDtsRecordByDtsId($dts_id);
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }
        }

        if ($this->session->has('dtsAttachment')) {
            $this->session->remove('dtsAttachment');
        }

        // 通知邮件
        $notice = $client['notice'];
        $title  = $client['title'];
        if (!empty($notice)) {
            $model  = new UserModel();
            $fields = ['email'];
            $result = $model->getUserRecordsByIds($fields, $notice);
            if (!empty($result)) {
                $emails = [];
                foreach ($result as $r) {
                    $emails[] = $r['email'];
                }

                $subject    = '【发自Mix】新问题单需要处理 DTS-' . $dts_id;
                $emailParam = [
                    'link'  => $this->selfConfig->dtsDetailsLink . $dts_id,
                    'ID'    => 'DTS-' . $dts_id,
                    'title' => $title,
                ];
                $emailMessage = view('dts/notice.php', $emailParam);

                $emailAPI = \Config\Services::email();
                $emailAPI->setFrom($emailAPI->SMTPUser);
                $emailAPI->setTo($emails);
                $emailAPI->setSubject($subject);
                $emailAPI->setMessage($emailMessage);
                if (!$emailAPI->send(false)) {
                    $err = $emailAPI->printDebugger('subject');
                    log_message('error', '{file}:{line} --> send mail failed ' . substr($phone, 0, 3) . '****' . substr($phone, 7, 4) . '.  ' . $err);
                    $res['msg'] = '已创建，但通知失败';
                    return $this->respond($res);
                }
            }
        }

        $this->_delCacheAfterUpdate($client['station_id']);

        $res['msg'] = '已创建';
        return $this->respond($res);
    }

    public function deleteOne()
    {
        if (!$this->validate('DtsDeleteOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

        $fields = ['station_id', 'place_at', 'created_at', 'updated_at'];
        $dts_id = $client['dts_id'];

        $model = new DtsModel();
        $db    = $model->getDtsRecordByDtsId($fields, $dts_id);
        if (empty($db)) {
            return $this->respond(['info' => 'empty']);
        }

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        if ($db['created_at'] != $db['updated_at']) {
            $isAdmin = $this->session->get('isAdmin');
            if ($isAdmin === false) {
                return $this->failUnauthorized('不能执行删除操作，请联系管理员删除');
            }
        }

        // 附件
        $fields = ['new_name'];
        $model2 = new DtsAttachmentModel();
        $files  = $model2->getDtsAttachmentRecordsByDtsId($fields, $dts_id);
        if (!empty($files)) {
            $path = WRITEPATH . $this->selfConfig->attachmentPath;
            foreach ($files as $v) {
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $v['new_name'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $result = $model2->delDtsAttachmentRecordsByDtsId($dts_id);
        }

        $result = $model->delDtsRecordByDtsId($dts_id);
        if (!$result) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $this->_delCacheAfterUpdate($db['station_id']);

        $res['msg'] = '已删除';
        return $this->respond($res);
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

        $resources = ['progress', 'to_suspend', 'to_cancel', 'score', 'to_resolve', 'to_close', 'back_work', 'req_edit'];
        if (!in_array($resource, $resources)) {
            return $this->fail('请求数据无效');
        }

        if ($resource === 'progress') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->respond(['msg' => '空白的新进展']);
            }

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = ['progress' => $text];

            // $wf = new WfDts($db['place_at']);
            // if ($wf->toWorking()) {
            //     $data['place_at'] = $wf->getTicket()->getCurrentPlace();
            // }

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            return $this->respond(['msg' => '已更新进展']);
        }

        if ($resource === 'req_edit') {
            $dts_id     = $client['dts_id'];
            $station_id = $client['station_id'];

            $data = [
                'type'        => $client['type'],
                'level'       => $client['level'],
                'title'       => $client['title'],
                'device'      => $client['device'],
                'description' => $client['description'],
                'progress'    => $client['progress'],
                'created_at'  => $client['created_at'],
            ];

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db) || $station_id !== $db['station_id']) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($station_id);
            return $this->respond(['msg' => '已保存']);
        }

        if ($resource === 'to_suspend') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->toSuspend()) {
                return $this->fail('不能切换问题单状态');
            }

            $text             = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data             = ['progress' => $text];
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($db['station_id']);

            $res['msg'] = '已挂起';
            return $this->respond($res);
        }

        if ($resource === 'to_cancel') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->toCancel()) {
                return $this->fail('不能切换问题单状态');
            }

            $text             = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data             = ['progress' => $text];
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($db['station_id']);

            $res['msg'] = '已取消';
            return $this->respond($res);
        }

        if ($resource === 'score') {
            $dts_id = $client['dts_id'];

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($db['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $data = [
                'score'      => $client['score'],
                'score_desc' => $client['score_desc'],
                'scored_by'  => $this->_getContentHeadTpl(),
            ];

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $res['msg'] = '已评分';
            return $this->respond($res);
        }

        if ($resource === 'to_resolve') {
            $dts_id         = $client['dts_id'];
            $progress       = $client['progress'];
            $cause          = $client['cause'];
            $cause_analysis = $client['cause_analysis'];
            if (empty($progress)) {
                return $this->fail('解决意见不能空白');
            }

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            $allowWorkflow    = $this->session->get('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_resolve', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->toResolve()) {
                return $this->fail('不能切换问题单状态');
            }

            $text = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data = [
                'progress'       => $text,
                'cause'          => $cause,
                'cause_analysis' => $cause_analysis,
            ];
            $data['place_at']    = $wf->getTicket()->getCurrentPlace();
            $data['resolved_at'] = date("Y-m-d H:i:s");

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($db['station_id']);

            $res['msg'] = '已提交解决';
            return $this->respond($res);
        }

        if ($resource === 'to_close') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->fail('关闭审核意见不能空白');
            }

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            $allowWorkflow    = $this->session->get('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_close', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->toClose()) {
                return $this->fail('不能切换问题单状态');
            }

            $text             = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data             = ['progress' => $text];
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($db['station_id']);

            $res['msg'] = '已提交关闭';
            return $this->respond($res);
        }

        if ($resource === 'back_work') {
            $dts_id   = $client['dts_id'];
            $progress = $client['progress'];
            if (empty($progress)) {
                return $this->fail('重新处理意见不能空白');
            }

            $model  = new DtsModel();
            $fields = ['id', 'dts_id', 'station_id', 'progress', 'place_at'];
            $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
            if (empty($db)) {
                return $this->fail('请求数据无效');
            }

            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            $allowWorkflow    = $this->session->get('allowWorkflow');
            if (!in_array($db['station_id'], $allowWriteDeptId) || !in_array('dts_back_work', $allowWorkflow)) {
                return $this->failUnauthorized('用户没有权限');
            }

            $wf = new WfDts($db['place_at']);
            if (!$wf->toBackWorking()) {
                return $this->fail('不能切换问题单状态');
            }

            $text             = $this->_getContentHeadTpl() . $progress . "\n" . $db['progress'];
            $data             = ['progress' => $text];
            $data['place_at'] = $wf->getTicket()->getCurrentPlace();

            $result = $model->updateDtsRecordById($data, $db['id']);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $this->_delCacheAfterUpdate($db['station_id']);

            $res['msg'] = '继续处理';
            return $this->respond($res);
        }

    }

    // Part-2
    protected function _reqNews()
    {
        if (!$this->validate('DtsReqNews')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $param = $this->request->getGet();

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($param['station_id'], $allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = [];
            return $res;
        } else {
            $query['station_id'] = [$param['station_id']];
        }

        $query['status'] = 'publish';
        $query['limit']  = $this->selfConfig->newsItemNum;
        $query['offset'] = 1;
        $fields          = ['id', 'dts_id', 'station_id', 'type', 'title', 'creator_id', 'place_at', 'updated_at'];

        $model  = new DtsModel();
        $result = $model->getDtsRecordsByUpdateDate($fields, $query);

        if (empty($result)) {
            $res['http_status'] = 200;
            $res['data']        = [];
            return $res;
        }

        $data  = $this->_getUserNameAndWorkflowName($result);
        $data  = $this->_getDeptName($data);
        $data2 = [];
        foreach ($data as $d) {
            $temp = [];
            foreach ($d as $key => $v) {
                if ($key === 'title') {
                    $temp['title_'] = $v;
                } else {
                    $temp[$key] = $v;
                }
            }
            $data2[] = $temp;
        }

        $res['http_status'] = 200;
        $res['data']        = $data2;
        return $res;
    }

    protected function _reqSearchParams()
    {
        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['station' => [], 'workflow' => []];
            return $res;
        }

        $typeItems = [
            ['id' => '0', 'name' => '全部'],
        ];
        $types = $this->selfConfig->typesMap;
        foreach ($types as $key => $t) {
            $typeItems[] = ['id' => strval($key), 'name' => $t];
        }

        $levelItems = [
            ['id' => '0', 'name' => '全部'],
        ];
        $levels = $this->selfConfig->levelsMap;
        foreach ($levels as $key => $t) {
            $levelItems[] = ['id' => strval($key), 'name' => $t];
        }

        $causeItems = $this->selfConfig->causes;
        array_unshift($causeItems, ['id' => '0', 'name' => '全部']);

        $model        = new DeptModel();
        $fields       = ['id', 'name'];
        $stationItems = $model->getDeptRecordsByIds($fields, $allowReadDeptId);
        array_unshift($stationItems, ['id' => '0', 'name' => '全部']);

        $wf            = new WfDts();
        $workflowItems = $wf->getPlaceMetaOfName();

        $pid         = $this->session->get('allowDefaultDeptId');
        $model       = new DeviceModel();
        $fields      = ['id', 'name'];
        $deviceItems = $model->getDeviceRecordsByPid($fields, $pid);
        array_unshift($deviceItems, ['id' => '0', 'name' => '全部']);

        $scoreItems = [
            ['id' => '0', 'name' => '全部'],
            ['id' => '1', 'name' => '大于0分'],
            ['id' => '2', 'name' => '小于0分'],
        ];

        $res['http_status'] = 200;
        $res['data']        = [
            'typeItems'     => $typeItems,
            'levelItems'    => $levelItems,
            'causeItems'    => $causeItems,
            'stationItems'  => $stationItems,
            'workflowItems' => $workflowItems,
            'deviceItems'   => $deviceItems,
            'scoreItems'    => $scoreItems,
        ];
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

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }

        // 1
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

        if (isset($param['place_at']) && !empty($param['place_at'])) {
            $wf       = new WfDts();
            $workflow = $wf->getPlaceAlias();
            foreach ($param['place_at'] as $p) {
                if (!in_array($p, $workflow)) {
                    $res['http_status'] = 400;
                    $res['msg']         = [
                        'error' => '请求数据无效',
                        'info'  => 'invalid wf place_at',
                    ];
                    return $res;
                }
            }
            //
            $query['place_at'] = $param['place_at'];
        }

        if ($param['level'] !== '0') {
            $query['level'] = $param['level'];
        }

        // 2
        if (isset($param['dts_id']) && $param['dts_id'] !== '') {
            if (preg_match('/^[1-9]\d{0,19}$/', $param['dts_id'])) {
                $query['dts_id'] = $param['dts_id'];
            }
        }

        if (isset($param['title']) && $param['title'] !== '') {
            $query['title'] = $param['title'];
        }

        if ($param['device'] !== '0') {
            $query['device'] = '+' . $param['device'] . '+';
        }

        if (isset($param['creator']) && $param['creator'] !== '') {
            if (preg_match('/^([\x{4e00}-\x{9fa5}]{1,6})$/u', $param['creator'])) {
                $model  = new UserModel();
                $fields = ['id'];
                $db     = $model->getUserRecordByUsername($fields, $param['creator']);

                if (!empty($db)) {
                    $query['creator_id'] = $db['id'];
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

        // 3
        if ($param['cause'] !== '0') {
            $query['cause'] = $param['cause'];
        }
        if ($param['score'] !== '0') {
            $query['score'] = $param['score'];
        }
        if (isset($param['created_range'])) {
            $temp = $param['created_range'];
            if (count($temp) === 2 && !empty($temp[0]) && !empty($temp[1])) {
                $query['created_start'] = $temp[0];
                $query['created_end']   = $temp[1];
            }
        }
        if (isset($param['updated_range'])) {
            $temp = $param['updated_range'];
            if (count($temp) === 2 && !empty($temp[0]) && !empty($temp[1])) {
                $query['updated_start'] = $temp[0];
                $query['updated_end']   = $temp[1];
            }
        }
        if (isset($param['resolved_range'])) {
            $temp = $param['resolved_range'];
            if (count($temp) === 2 && !empty($temp[0]) && !empty($temp[1])) {
                $query['resolved_start'] = $temp[0];
                $query['resolved_end']   = $temp[1];
            }
        }

        // $res['http_status'] = 200;
        // $res['data']        = ['total' => 0, 'data' => [], 'query' => $query];
        // return $res;

        $query['status'] = 'publish';
        $query['limit']  = $param['limit'];
        $query['offset'] = $param['offset'];
        $fields          = ['id', 'dts_id', 'station_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator_id'];

        $model  = new DtsModel();
        $result = $model->getDtsRecordsByMultiConditions($fields, $query);

        if ($result['total'] === 0) {
            $res['http_status'] = 200;
            $res['data']        = $result;
            return $res;
        }

        // id => name
        $data = $this->_getUserNameAndWorkflowName($result['data']);
        $data = $this->_getDeptName($data);

        $res['http_status'] = 200;
        $res['data']        = ['total' => $result['total'], 'data' => $data];
        return $res;
    }

    protected function _reqNewForm()
    {
        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (empty($allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $station_id = $this->session->get('allowDefaultDeptId');
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

        $description = $this->selfConfig->progressTemplates['new_form'];

        $model   = new DeptModel();
        $fields  = ['id', 'name'];
        $station = $model->getDeptRecordsByIds($fields, $allowWriteDeptId);

        $dept    = '+' . $station_id . '+';
        $model   = new UserModel();
        $fields  = ['id', 'username'];
        $workers = $model->getUserRecordByDept($fields, $dept);

        $res['http_status'] = 200;
        $res['data']        = [
            'description' => $description,
            'deviceList'  => $deviceList,
            'station'     => $station,
            'workers'     => $workers,
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

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
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

        $fields = ['dts_id', 'type', 'title', 'level', 'station_id', 'place_at', 'device', 'description', 'progress', 'created_at', 'updated_at', 'creator_id', 'reviewer_id', 'score', 'score_desc', 'scored_by', 'cause', 'cause_analysis'];
        $model  = new DtsModel();
        $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
        if (empty($db)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($db['station_id'], $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }
        $details = $db;

        // id -> name
        $details['type']  = $this->selfConfig->typesMap[$db['type']];
        $details['level'] = $this->selfConfig->levelsMap[$db['level']];

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
            'progressTemplates' => $this->selfConfig->progressTemplates,
            'causes'            => $this->selfConfig->causes,
            'operation'         => $operation,
            'attachments'       => $attachments,
        ];
        return $res;
    }

    protected function _preEdit()
    {
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

        $fields = ['dts_id', 'type', 'title', 'level', 'station_id', 'device', 'description', 'progress', 'created_at'];
        $model  = new DtsModel();
        $db     = $model->getDtsRecordByDtsId($fields, $dts_id);
        if (empty($db)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($db['station_id'], $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }
        $details = $db;

        $details['station'] = $this->_getDeptName2($db['station_id']);
        $deviceList         = $this->_getDeviceList($db['station_id']);

        $res['http_status'] = 200;
        $res['data']        = [
            'record'     => $details,
            'deviceList' => $deviceList,
        ];
        return $res;
    }

    protected function _reqStatisticChartData()
    {
        if (!$this->validate('DtsReqStatisticChartData')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];

        // try {
        //     $cache     = new MyCache($this->selfConfig->cachePrefix['statistic_chart']);
        //     $key       = 'station_id=' . $station_id;
        //     $cacheData = $cache->getCache($key);
        //     if (!empty($cacheData)) {
        //         $res['http_status'] = 200;
        //         $res['data']        = $cacheData;
        //         return $res;
        //     }
        // } catch (RedisException $e) {
        // }

        try {
            $cache     = \Config\Services::cache();
            $key       = $this->selfConfig->cacheStatisticChart['prefix'] . 'station_id:' . $station_id;
            $cacheData = $cache->get($key);
            if (!empty($cacheData)) {
                $res['http_status'] = 200;
                $res['data']        = $cacheData;
                return $res;
            }
        } catch (CriticalError $e) {
        }

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        //
        $allTypes     = $this->_getTypeStatistic($station_id);
        $distribution = $this->_getDistributionStatistic($station_id);
        //
        $createList  = $this->_getCreateStatistic($station_id);
        $resolveList = $this->_getResolveStatistic($station_id);
        //
        $total = 0;
        foreach ($allTypes as $v) {
            $total = $total + $v['value'];
        }
        $longTerm = $this->_getLongTermStatistic($station_id, $total);
        //
        $defectLevel = $this->_getDefectLevelStatistic($station_id);
        $defectWf    = $this->_getDefectWfPlaceStatistic($station_id);
        //
        $hiddenDangerLevel = $this->_getHiddenDangerLevelStatistic($station_id);
        $hiddenDangerWf    = $this->_getHiddenDangerWfPlaceStatistic($station_id);
        //
        $cause = $this->_getCauseStatistic($station_id);

        $data = [
            'allTypes'          => $allTypes,
            'distribution'      => $distribution,
            'createList'        => $createList,
            'resolveList'       => $resolveList,
            'longTerm'          => $longTerm,
            'defectLevel'       => $defectLevel,
            'hiddenDangerLevel' => $hiddenDangerLevel,
            'defectWf'          => $defectWf,
            'hiddenDangerWf'    => $hiddenDangerWf,
            'cause'             => $cause,
        ];

        if (isset($cache)) {
            $cache->save($key, $data, $this->selfConfig->cacheStatisticChart['expire']);
        }

        $res['http_status'] = 200;
        $res['data']        = $data;
        return $res;
    }

    protected function _reqAttachmentsList()
    {
        if (!$this->validate('DtsReqAttachmentsList')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $param = $this->request->getGet();

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }

        // 1
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

        if ($param['filename'] !== '') {
            $query['org_name'] = $param['filename'];
        }

        $query['limit']  = $param['limit'];
        $query['offset'] = $param['offset'];
        $fields          = ['id', 'station_id', 'dts_id', 'username', 'org_name', 'created_at'];

        $model  = new DtsAttachmentModel();
        $result = $model->getDtsAttachmentsByMultiConditions($fields, $query);

        if ($result['total'] === 0) {
            $res['http_status'] = 200;
            $res['data']        = $result;
            return $res;
        }

        // id => name
        // $data = $this->_getUserNameAndWorkflowName($result['data']);
        // $data = $this->_getDeptName($result['data']);

        $res['http_status'] = 200;
        $res['data']        = ['total' => $result['total'], 'data' => $result['data']];
        return $res;
    }

    // Part-3
    protected function _getDeviceList(int $station_id = 0)
    {
        if ($station_id <= 0) {
            return [];
        }
        $list = [];

        $model  = new DeviceModel();
        $fields = ['id', 'pid', 'name'];
        $list   = $model->getDeviceRecordsExcludeFirst($fields);

        return $list;
    }

    protected function _getContentHeadTpl()
    {
        return date('Y-m-d H:i', time()) . ' ' . $this->session->get('username') . "\n";
    }

    protected function _getUserNameAndWorkflowName(array $array = null)
    {
        if (empty($array)) {
            return [];
        }

        $model  = new UserModel();
        $fields = ['id', 'username', 'status'];
        $users  = $model->getUserAllRecords($fields);

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

        $model  = new DeptModel();
        $fields = ['id', 'name', 'status'];
        $dept   = $model->getDeptAllRecords($fields);

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

        $model  = new DeptModel();
        $fields = ['id', 'name', 'status'];
        $dept   = $model->getDeptAllRecords($fields);

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

        $model  = new UserModel();
        $fields = ['id', 'username', 'status'];
        $users  = $model->getUserRecordsByIds($fields, $ids);

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

        $ids = explode("+", trim($keys, '+'));
        $res = '';
        if (!empty($ids)) {
            $model   = new DeviceModel();
            $fields  = ['id', 'name'];
            $devices = $model->getDeviceRecordsByIds($fields, $ids);

            if (!empty($devices)) {
                $text = '';
                foreach ($ids as $value) {
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

        $wf = new WfDts();

        $metas = [];
        switch ($place) {
            case 'suspend':
                $metas = $wf->getPlaceLine('suspend');
                break;
            case 'cancel':
                $metas = $wf->getPlaceLine('cancel');
                break;
            default:
                $metas = $wf->getPlaceLine('main');
                break;
        }

        // if ($place !== 'suspend') {
        //     // $metas = $wf->getPlaceMainLine();
        //     $metas = $wf->getPlaceLine('main');
        // } else {
        //     $metas = $wf->getPlaceLine('suspend');
        // }

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
            // if ($i === 0) {
            //     $temp[$meta['alias']] = [
            //         'title'       => $meta['name'],
            //         'icon'        => false,
            //         'description' => [$creator, $created_at],
            //     ];
            // } else {
            $temp[$meta['alias']] = [
                'title'       => $meta['name'],
                'icon'        => false,
                'description' => [],
            ];
            // }
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
            'allowSuspend'          => false,
            'allowCancel'           => false,
            'allowScore'            => false,
            'allowResolve'          => false,
            'allowClose'            => false,
            'allowBackWork'         => false,
            'showRmvAttachmentIcon' => false,
        ];

        if ($place_at === 'suspend') {
            return [
                'allowUpdateProgress'   => false,
                'allowSuspend'          => false,
                'allowCancel'           => false,
                'allowScore'            => false,
                'allowResolve'          => false,
                'allowClose'            => false,
                'allowBackWork'         => true,
                'showRmvAttachmentIcon' => false,
            ];
        }

        if ($place_at === 'cancel') {
            return [
                'allowUpdateProgress'   => false,
                'allowSuspend'          => false,
                'allowCancel'           => false,
                'allowScore'            => false,
                'allowResolve'          => false,
                'allowClose'            => false,
                'allowBackWork'         => false,
                'showRmvAttachmentIcon' => false,
            ];
        }

        $wf  = new WfDts($place_at);
        $ops = $wf->getPlaceAllowOp();

        $result['allowUpdateProgress']   = isset($ops['updateProgress']) ? $ops['updateProgress'] : false;
        $result['allowSuspend']          = isset($ops['suspend']) ? $ops['suspend'] : false;
        $result['allowCancel']           = isset($ops['cancel']) ? $ops['cancel'] : false;
        $result['showRmvAttachmentIcon'] = isset($ops['showRmvAttachmentIcon']) ? $ops['showRmvAttachmentIcon'] : false;

        if ($this->session->has('allowWorkflow')) {
            $allowWorkflow        = $this->session->get('allowWorkflow');
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

        $fields = ['id', 'org_name'];
        $model  = new DtsAttachmentModel();
        $files  = $model->getDtsAttachmentRecordsByDtsId($fields, $dts_id);

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

    protected function _getTypeStatistic(string $station_id = null)
    {
        $result = [];
        $types  = $this->selfConfig->typesMap;
        foreach ($types as $key => $t) {
            $result[] = ['id' => strval($key), 'type' => $t, 'value' => 0];
        }

        if (empty($station_id)) {
            return $result;
        }

        $model = new DtsModel();
        $data  = $model->getByStationAndGroupByType($station_id);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['id'] === $d['type']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getDistributionStatistic(string $station_id = null)
    {
        $result = [];
        if (empty($station_id)) {
            return $result;
        }

        $model      = new DeviceModel();
        $fields     = ['id', 'name'];
        $deviceUnit = $model->getDeviceRecordsByPid($fields, $station_id);
        if (!empty($deviceUnit)) {
            foreach ($deviceUnit as $d) {
                $result[] = [
                    'id'       => $d['id'],
                    'unit'     => $d['name'],
                    'place_at' => '处理中',
                    'value'    => 0,
                ];
            }
            foreach ($deviceUnit as $d) {
                $result[] = [
                    'id'       => $d['id'],
                    'unit'     => $d['name'],
                    'place_at' => '挂起',
                    'value'    => 0,
                ];
            }
        }

        $length = count($deviceUnit);
        $wf     = new WfDts();

        $model = new DtsModel();
        foreach ($deviceUnit as $key => $d) {
            $device_id = '+' . $d['id'] . '+';
            $data      = $model->getByStationDeviceAndGroupByPlace($station_id, $device_id);
            foreach ($data as $item) {
                $name = $wf->getPlaceName($item['place_at']);
                if ($name === $result[$key]['place_at']) {
                    $result[$key]['value'] = intval($item['value']);
                } else if ($name === $result[$key + $length]['place_at']) {
                    $result[$key + $length]['value'] = intval($item['value']);
                }
            }
        }

        return $result;
    }

    protected function _getLongTermStatistic(string $station_id = null, int $total = null)
    {
        $chart = [
            'title'    => '比率',
            'ranges'   => [10, 70, 100],
            'measures' => [0],
            'target'   => 10,
        ];

        if (empty($station_id) || empty($total)) {
            return [
                'chart'       => [$chart],
                'workingRate' => 0,
            ];
        }

        $days = $this->selfConfig->longTermDays;

        $model       = new DtsModel();
        $data        = $model->getByStationAndCreatedExceedDaysGroupByPlace($station_id, $days);
        $num         = 0;
        $workingRate = 0;
        foreach ($data as $d) {
            $num = $num + intval($d['value']);
            if ($d['place_at'] === 'working') {
                $workingRate = ceil(intval($d['value']) * 100 / $total);
            }
        }
        $chart['measures'][0] = intval($num * 100 / $total);

        return [
            'chart'       => [$chart],
            'workingRate' => $workingRate,
        ];
    }

    protected function _getCreateStatistic(string $station_id = null)
    {
        $result = [];

        $year = date('Y', time());
        for ($i = 1; $i < 13; $i++) {
            if ($i < 10) {
                $result[] = [
                    'date'  => $year . '-0' . $i,
                    'value' => 0,
                ];
            } else {
                $result[] = [
                    'date'  => $year . '-' . $i,
                    'value' => 0,
                ];
            }

        }

        $model = new DtsModel();
        $data  = $model->getByStationYearGroupByCreateMonth($station_id, $year);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['date'] === $d['date']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getResolveStatistic(string $station_id = null)
    {
        $result = [];

        $year = date('Y', time());
        for ($i = 1; $i < 13; $i++) {
            if ($i < 10) {
                $result[] = [
                    'date'  => $year . '-0' . $i,
                    'value' => 0,
                ];
            } else {
                $result[] = [
                    'date'  => $year . '-' . $i,
                    'value' => 0,
                ];
            }

        }

        $model = new DtsModel();
        $data  = $model->getByStationYearResolveGroupByResolveMonth($station_id, $year);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['date'] === $d['date']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getDefectLevelStatistic(string $station_id = null)
    {
        $result = [];

        $level = $this->selfConfig->levelsMap;
        foreach ($level as $key => $l) {
            $result[] = [
                'id'    => strval($key),
                'level' => $l,
                'value' => 0,
            ];
        }
        $type = '2';

        $model = new DtsModel();
        $data  = $model->getByStationTypeGroupByLevel($station_id, $type);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['id'] === $d['level']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getDefectWfPlaceStatistic(string $station_id = null)
    {
        $result = [
            ['id' => 'working', 'place_at' => '处理中', 'value' => 0],
            ['id' => 'suspend', 'place_at' => '挂起', 'value' => 0],
        ];

        $type  = '2';
        $model = new DtsModel();
        $data  = $model->getByStationTypeGroupByWfPlace($station_id, $type);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['id'] === $d['place_at']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getHiddenDangerLevelStatistic(string $station_id = null)
    {
        $result = [];

        $level = $this->selfConfig->levelsMap;
        foreach ($level as $key => $l) {
            $result[] = [
                'id'    => strval($key),
                'level' => $l,
                'value' => 0,
            ];
        }
        $type = '1';

        $model = new DtsModel();
        $data  = $model->getByStationTypeGroupByLevel($station_id, $type);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['id'] === $d['level']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getHiddenDangerWfPlaceStatistic(string $station_id = null)
    {
        $result = [
            ['id' => 'working', 'place_at' => '处理中', 'value' => 0],
            ['id' => 'suspend', 'place_at' => '挂起', 'value' => 0],
        ];

        $type  = '1';
        $model = new DtsModel();
        $data  = $model->getByStationTypeGroupByWfPlace($station_id, $type);
        foreach ($data as $d) {
            foreach ($result as $key => $r) {
                if ($r['id'] === $d['place_at']) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _getCauseStatistic(string $station_id = null)
    {
        $result = [];

        $causes = $this->selfConfig->causes;
        foreach ($causes as $d) {
            $result[] = [
                'id'    => $d['id'],
                'cause' => $d['name'],
                'type'  => '全部',
                'value' => 0,
            ];
        }
        foreach ($causes as $d) {
            $result[] = [
                'id'    => $d['id'],
                'cause' => $d['name'],
                'type'  => '今年',
                'value' => 0,
            ];
        }

        if (empty($station_id)) {
            return $result;
        }

        $model = new DtsModel();
        //
        $data = $model->getByStationGroupByCause($station_id);
        foreach ($causes as $key => $c) {
            $cause_id = $c['id'];
            foreach ($data as $d) {
                if ($d['cause'] == $cause_id) {
                    $result[$key]['value'] = intval($d['value']);
                }
            }
        }
        //
        $length = count($causes);
        $year   = date('Y', time());
        $data   = $model->getByStationYearGroupByCause($station_id, $year);
        foreach ($causes as $key => $c) {
            $cause_id = $c['id'];
            foreach ($data as $d) {
                if ($d['cause'] == $cause_id) {
                    $result[$key + $length]['value'] = intval($d['value']);
                }
            }
        }

        return $result;
    }

    protected function _delCacheAfterUpdate(string $station_id = '*')
    {
        // try {
        //     $cache = new MyCache($this->selfConfig->cachePrefix['statistic_chart']);
        //     $key   = 'station_id=' . $station_id;
        //     $cache->delCache($key);
        // } catch (RedisException $e) {
        // }

        $cache = \Config\Services::cache();
        $key   = $this->selfConfig->cacheStatisticChart['prefix'] . 'station_id:' . $station_id;
        return $cache->delete($key);
    }
}
