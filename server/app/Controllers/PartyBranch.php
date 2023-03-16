<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-16 21:25:54
 */

namespace App\Controllers;

use App\Models\Common\DeptModel;
//
use App\Models\Doc\Category as CategoryModel;
use App\Models\Drawing\Basic as DrawingModel;
use App\Models\PartyBranch\Basic as PartyBranchModel;
//
use CodeIgniter\API\ResponseTrait;
//
//
use CodeIgniter\Files\Exceptions\FileException;

class PartyBranch extends BaseController
{
    use ResponseTrait;

    protected $config;

    public function __construct()
    {
        $this->config = config('Config\\MyConfig\\DocStore');
    }

    public function queryEntry()
    {
        $resource = $this->request->getGet('resource');
        if (empty($resource)) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        switch ($resource) {
            case 'search_options':
                $result = $this->_reqSearchOptions();
                break;
            case 'list':
                $result = $this->_reqList();
                break;
            case 'blank_form':
                $result = $this->_repBlankForm();
                break;
            case 'details':
                $result = $this->_reqDetails();
                break;
            case 'edit':
                $result = $this->_reqEdit();
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

    // 2023-2-21
    public function uploadFile()
    {
        if (!$this->validate('DrawingUploadFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $id   = $this->request->getPost('id');
        $key  = $this->request->getPost('key');
        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->failServerError('文件上传失败');
        }

        if (!$file->getSize() > $this->config->maxFileSize) {
            return $this->fail($this->config->fileExceedSizeMsg);
        }
        if (!$file->getSize() === 0) {
            return $this->fail('上传空文件');
        }

        $fileType = $file->getMimeType();
        if (!in_array($fileType, $this->config->allowedFileTypes)) {
            return $this->fail($this->config->fileInvalidTypeMsg);
        }

        $file_org_name  = $file->getName();
        $file_new_name  = $file->getRandomName();
        $file_ext       = $file->guessExtension();
        $file_mime_type = $file->getMimeType();
        $size           = $file->getSize();

        // 注意
        if ($key === 'create') {
            $path = WRITEPATH . $this->config->tempPath;
            if (!$file->hasMoved()) {
                try {
                    $file->move($path, $file_new_name, true);
                } catch (FileException $exception) {
                    return $this->failServerError('保存文件出错');
                }
            }
            //
            $cache = $this->session->get('drawingFileCache');
            $time  = time();
            $data  = [
                'id'             => $time,
                'file_org_name'  => $file_org_name,
                'file_new_name'  => $file_new_name,
                'file_ext'       => $file_ext,
                'file_mime_type' => $file_mime_type,
                'size'           => $size,
            ];
            if (!$cache) {
                $this->session->set('drawingFileCache', [$data]);
            } else {
                $this->session->push('drawingFileCache', [$data]);
            }

            $res['id']   = $time;
            $res['id22'] = $file_mime_type;
            return $this->respond($res);
        }
        // 注意
        if ($key === 'update') {
            // 与数据库的记录比较
            $model  = new DrawingModel();
            $fields = ['station_id', 'file_new_name'];
            $old    = $model->getRecordById($fields, $id);
            if (empty($old)) {
                $res['error'] = '请求对象不存在';
                return $this->fail($res);
            }

            // 用户是否能修改
            $allowWriteDeptId = $this->session->get('allowWriteDeptId');
            if (!in_array($old['station_id'], $allowWriteDeptId)) {
                return $this->failUnauthorized('用户没有权限');
            }

            if (!empty($old['file_new_name'])) {
                $res['error'] = '上传文件冲突，请刷新后尝试';
                return $this->fail($res);
            }

            $path = WRITEPATH . $this->config->filePath;
            if (!$file->hasMoved()) {
                try {
                    $file->move($path, $file_new_name, true);
                } catch (FileException $exception) {
                    return $this->failServerError('保存文件出错');
                }
            }

            $uid      = $this->session->get('id');
            $username = $this->session->get('username');
            //
            $draft = [
                'file_org_name'  => $file_org_name,
                'file_new_name'  => $file_new_name,
                'file_ext'       => $file_ext,
                'file_mime_type' => $file_mime_type,
                'size'           => $size,
                'user_id'        => $uid,
                'username'       => $username,
            ];

            $result = $model->updateRecordById($draft, $id);
            if ($result === false) {
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $file_new_name;
                if (file_exists($file)) {
                    unlink($file);
                }
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $res['msg'] = '完成文件上传';
            return $this->respond($res);
        }

    }

    // 2023-2-21
    public function deleteFile()
    {
        if (!$this->validate('DrawingDeleteFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $key    = $client['key'];
        $id     = $client['id'];

        // 新建
        if ($key === 'create') {
            $temp = $this->session->get('drawingFileCache');
            if (empty($temp)) {
                return $this->respond(['res' => 'empty']);
            }

            $path = WRITEPATH . $this->config->tempPath;
            $new  = [];
            foreach ($temp as $v) {
                if ($id == $v['id']) {
                    $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $v['file_new_name'];
                    if (file_exists($file)) {
                        unlink($file);
                    }
                } else {
                    $new[] = $v;
                }
            }
            $this->session->remove('drawingFileCache');
            if (!empty($new)) {
                $this->session->set('drawingFileCache', $new);
            }

            return $this->respond(['res' => 'done']);
        }

        // 修改
        if ($key === 'update') {
            $file_org_name = $client['file_org_name'];
            //
            $fields = ['file_org_name', 'file_new_name'];
            $model  = new DrawingModel();
            $db     = $model->getRecordById($fields, $id);
            if (empty($db)) {
                return $this->respond(['res' => 'done']);
            }
            if ($db['file_org_name'] !== $file_org_name) {
                return $this->failServerError('请求错误，刷新页面后再试');
            }
            //
            $uid      = $this->session->get('id');
            $username = $this->session->get('username');
            $draft    = [
                'file_org_name'  => '',
                'file_new_name'  => '',
                'file_ext'       => '',
                'file_mime_type' => '',
                'size'           => 0,
                'user_id'        => $uid,
                'username'       => $username,
            ];
            $result = $model->updateRecordById($draft, $id);
            if ($result === false) {
                return $this->failServerError('服务器处理发生错误，稍候再试');
            }

            $path = WRITEPATH . $this->config->filePath;
            $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['file_new_name'];
            if (file_exists($file)) {
                unlink($file);
            }

            return $this->respond(['res' => 'done']);
        }
    }

    // 2023-2-22
    public function createOne()
    {
        if (!$this->validate('PartyBranchCreateOne')) {
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
        $uid      = $this->session->get('id');
        $username = $this->session->get('username');
        $draft    = [
            'station_id'  => $client['station_id'],
            'category_id' => $client['category_id'],
            'dwg_name'    => $client['dwg_name'],
            'keywords'    => $client['keywords'],
            'info'        => $client['info'],
            'user_id'     => $uid,
            'username'    => $username,
        ];

        // 注意
        $draft['deleted'] = 0;
        // serial_id
        $query['dwg_num'] = $client['dwg_num'];
        $fields           = ['serial_id'];
        $model            = new DrawingModel();
        $serial_id        = $model->getLastRecordByDocNum($fields, $query) + 1;

        $draft['serial_id'] = $serial_id;
        $draft['dwg_num']   = $client['dwg_num'] . '-' . sprintf("%03d", $serial_id);

        // 文件
        $files     = $client['files'];
        $fileCache = $this->session->get('drawingFileCache');
        $temp      = [];
        if (!empty($files) && !empty($fileCache)) {
            foreach ($files as $f) {
                foreach ($fileCache as $c) {
                    if ($f['id'] == $c['id']) {
                        $temp[] = $c;
                    }
                }
            }
        }
        // 注意
        if (count($temp) == 1) {
            $t = $temp[0];
            //
            $draft['file_org_name']  = $t['file_org_name'];
            $draft['file_new_name']  = $t['file_new_name'];
            $draft['file_ext']       = $t['file_ext'];
            $draft['file_mime_type'] = $t['file_mime_type'];
            $draft['size']           = $t['size'];
            //
            $tempPath = WRITEPATH . $this->config->tempPath;
            $filePath = WRITEPATH . $this->config->filePath;
            $old      = rtrim($tempPath, '\\/ ') . DIRECTORY_SEPARATOR . $t['file_new_name'];
            $new      = rtrim($filePath, '\\/ ') . DIRECTORY_SEPARATOR . $t['file_new_name'];
            rename($old, $new);
        }

        if ($this->session->has('drawingFileCache')) {
            $this->session->remove('drawingFileCache');
        }

        $result = $model->insertOneRecord($draft);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['msg'] = '创建完成';
        return $this->respond($res);
    }

    // 2023-2-22
    public function downloadFile()
    {
        if (!$this->validate('DrawingDownloadFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $params        = $this->request->getGet();
        $id            = $params['id'];
        $file_org_name = $params['file_org_name'];

        $fields = ['station_id', 'file_org_name', 'file_new_name', 'file_mime_type'];
        $model  = new DrawingModel($fields, $id);
        $db     = $model->getRecordById($fields, $id);

        if (empty($db)) {
            return $this->failNotFound('文件不存在');
        }
        if ($db['file_org_name'] !== $file_org_name) {
            return $this->failNotFound('文件不存在');
        }

        $allowReadDeptId = $this->session->get('allowReadDeptId');
        if (!in_array($db['station_id'], $allowReadDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $path = WRITEPATH . $this->config->filePath;
        $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['file_new_name'];
        if (!file_exists($file)) {
            return $this->failNotFound('文件不存在');
        }

        // $type = filetype($file);
        $type = $db['file_mime_type'];
        header("Content-type:" . $type);
        header("Content-Disposition: attachment; filename=" . urlencode($db['file_org_name']));
        header("Content-Transfer-Encoding: binary");
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file);
        exit;
    }

    // 2023-2-23
    public function updateOne()
    {
        if (!$this->validate('DrawingUpdateOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $id     = $client['id'];

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($client['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }
        //
        $model  = new DrawingModel();
        $fields = ['station_id', 'category_id'];
        $old    = $model->getRecordById($fields, $id);
        if (empty($old)) {
            $res['error'] = '请求对象不存在';
            return $this->fail($res);
        }
        //
        $uid      = $this->session->get('id');
        $username = $this->session->get('username');
        $draft    = [
            'station_id'  => $client['station_id'],
            'category_id' => $client['category_id'],
            'dwg_name'    => $client['dwg_name'],
            'keywords'    => $client['keywords'],
            'info'        => $client['info'],
            'user_id'     => $uid,
            'username'    => $username,
        ];

        // 注意
        // $draft['deleted'] = 0;
        // serial_id
        // 当站点和类别被修改，重新计算serial_id
        if ($client['station_id'] != $old['station_id'] || $client['category_id'] != $old['category_id']) {
            $query['dwg_num']   = $client['dwg_num'];
            $fields             = ['serial_id'];
            $serial_id          = $model->getLastRecordByDocNum($fields, $query) + 1;
            $draft['serial_id'] = $serial_id;
            $draft['dwg_num']   = $client['dwg_num'] . '-' . sprintf("%03d", $serial_id);
        }

        $result = $model->updateRecordById($draft, $id);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['msg'] = '修改完成';
        return $this->respond($res);
    }

    // 2023-2-23
    public function deleteOne()
    {
        if (!$this->validate('DrawingDeleteOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

        $fields = ['station_id', 'file_new_name'];
        $id     = $client['id'];

        $model = new DrawingModel();
        $db    = $model->getRecordById($fields, $id);
        if (empty($db)) {
            return $this->respond(['info' => 'empty']);
        }

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        // 注意
        $uid      = $this->session->get('id');
        $username = $this->session->get('username');
        $draft    = [
            'file_org_name'  => '',
            'file_new_name'  => '',
            'file_ext'       => '',
            'file_mime_type' => '',
            'size'           => 0,
            'user_id'        => $uid,
            'username'       => $username,
            // 注意
            'deleted'        => 1,
        ];

        $result = $model->updateRecordById($draft, $id);
        if (!$result) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 文件
        $file_new_name = $db['file_new_name'];
        if (!empty($file_new_name)) {
            $path = WRITEPATH . $this->config->filePath;
            $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $file_new_name;
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $res['msg'] = '完成删除';
        return $this->respond($res);
    }

    // 2023-2-26
    protected function _reqSearchOptions()
    {
        $defaultStationId = $this->session->get('allowDefaultDeptId');
        if (empty($defaultStationId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $model  = new DeptModel();
        $fields = ['id', 'name'];
        // 注意
        $stationItems = $model->getDeptRecordsByIds($fields, [$defaultStationId]);
        // 注意 数组下标
        $CATEGORY          = $this->config->CATEGORY[$defaultStationId];
        $PARTY_BRANCH_CODE = $this->config->PARTY_BRANCH_CODE;

        $pid = 0;
        foreach ($CATEGORY as $v) {
            if ($v['code'] === $PARTY_BRANCH_CODE) {
                $pid = $v['id'];
                break;
            }
        }

        $categoryItems = [];
        if ($pid != 0) {
            helper('my_array');
            $categoryItems = my_get_all_children($CATEGORY, $pid);
        }
        array_unshift($categoryItems, ['id' => '0', 'name' => '全部', 'code' => '0']);

        $res['http_status'] = 200;
        $res['data']        = [
            'stationItems'  => $stationItems,
            'categoryItems' => $categoryItems,
        ];
        return $res;
    }

    // 2023-2-26
    protected function _reqList()
    {
        if (!$this->validate('PartBranchReqList')) {
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

        //
        if (!in_array($param['station_id'], $allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }
        // 注意
        $query['station_id'] = [$param['station_id']];

        if ($param['category_id'] !== '0') {
            $query['category_id'] = $param['category_id'];
        }
        if (isset($param['title']) && $param['title'] !== '') {
            $query['title'] = $param['title'];
        }
        if (isset($param['keywords']) && $param['keywords'] !== '') {
            $query['keywords'] = $param['keywords'];
        }

        // 注意
        $query['deleted'] = 0;
        $query['limit']   = $param['limit'];
        $query['offset']  = $param['offset'];
        $fields           = ['id', 'title', 'doc_num', 'category_id', 'secret_level', 'retention_period', 'file_org_name', 'paper_place', 'username', 'updated_at'];

        $model  = new PartyBranchModel();
        $result = $model->getRecordsByMultiConditions($fields, $query);

        if ($result['total'] === 0) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }

        // id => name
        // 注意：数组下标
        $categoryItems = $this->config->CATEGORY[$param['station_id']];
        $cnt           = count($result['data']);
        for ($i = 0; $i < $cnt; $i++) {
            $result['data'][$i]['category']         = $this->_getNameMap($categoryItems, $result['data'][$i]['category_id']);
            $result['data'][$i]['secret_level']     = $this->_getNameMap($this->config->SECRET_LEVEL, $result['data'][$i]['secret_level']);
            $result['data'][$i]['retention_period'] = $this->_getNameMap($this->config->RETENTION_PERIOD, $result['data'][$i]['retention_period']);
        }

        $res['http_status'] = 200;
        $res['data']        = ['total' => $result['total'], 'data' => $result['data']];
        return $res;
    }

    // 2023-3-2
    protected function _repBlankForm()
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

        $model  = new DeptModel();
        $fields = ['id', 'name'];
        //! 注意：用户只能在默认部门，新增记录
        $stationItems[] = $model->getDeptRecordById($fields, $station_id);

        //
        $model  = new CategoryModel();
        $fields = ['id'];
        $wheres = ['pid' => $station_id, 'alias' => 'PARTY_BRANCH'];
        $db     = $model->getByWheres($fields, $wheres);

        $categoryItems = [];
        if (!empty($db)) {
            $pid        = $db[0]['id'];
            $categories = $model->getChildrenByPid([], $pid);
            helper('my_array');
            $categoryItems = my_arr2tree($categories);
        }
        //
        $secretLevelItems     = $this->config->SECRET_LEVEL;
        $retentionPeriodItems = $this->config->RETENTION_PERIOD;

        $res['http_status'] = 200;
        $res['data']        = [
            'stationItems'         => $stationItems,
            'categoryItems'        => $categoryItems,
            'secretLevelItems'     => $secretLevelItems,
            'retentionPeriodItems' => $retentionPeriodItems,
        ];
        return $res;
    }

    // 2023-2-22
    protected function _reqDetails()
    {
        if (!$this->validate('DrawingReqDetails')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params = $this->request->getGet();
        $id     = $params['id'];

        $fields = ['id', 'station_id', 'dwg_name', 'category_id', 'dwg_num', 'keywords', 'info', 'file_org_name', 'file_ext', 'username', 'created_at', 'updated_at'];
        $model  = new DrawingModel();
        $db     = $model->getRecordById($fields, $id);
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
        $model  = new CategoryModel();
        $fields = ['name'];
        $temp   = $model->getById($fields, $db['category_id']);
        if (empty($temp)) {
            $details['category'] = '';
        } else {
            $details['category'] = $temp['name'];
        }

        $model  = new DeptModel();
        $fields = ['name'];
        $temp   = $model->getDeptRecordById($fields, $db['station_id']);
        if (empty($temp)) {
            $details['station'] = '';
        } else {
            $details['station'] = $temp['name'];
        }

        $res['http_status'] = 200;
        $res['data']        = $details;
        return $res;
    }

    // 2023-2-22
    protected function _reqEdit()
    {
        if (!$this->validate('DrawingReqEdit')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params = $this->request->getGet();
        $id     = $params['id'];

        $fields = ['id', 'station_id', 'dwg_name', 'category_id', 'dwg_num', 'keywords', 'info', 'file_org_name', 'file_new_name'];
        $model  = new DrawingModel();
        $db     = $model->getRecordById($fields, $id);
        if (empty($db)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }
        $record = $db;

        $model        = new DeptModel();
        $fields       = ['id', 'name', 'alias'];
        $stationItems = $model->getDeptRecordsByIds($fields, $allowWriteDeptId);

        $model         = new CategoryModel();
        $fields        = ['id', 'name', 'alias'];
        $categoryItems = $model->getAll($fields);

        if (empty($db['file_org_name'])) {
            $files = [];
        } else {
            $files[] = [
                'uid'      => $db['id'],
                'name'     => $db['file_org_name'],
                'status'   => 'done',
                'response' => ['id' => $db['id']],
            ];
        }

        $res['http_status'] = 200;
        $res['data']        = [
            'record'        => $record,
            'stationItems'  => $stationItems,
            'categoryItems' => $categoryItems,
            'files'         => $files,
        ];

        return $res;
    }

    // 2023-2-26 公共方法
    protected function _getNameMap(array $map = null, $id = 0, $key = 'id')
    {
        $name = '';
        foreach ($map as $v) {
            // 注意 ==
            if ($v[$key] == $id) {
                $name = $v['name'];
                break;
            }
        }
        return $name;
    }

}
