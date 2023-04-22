<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-04-22 16:08:28
 */

namespace App\Controllers;

use App\Libraries\MyIdMaker;
use App\Models\Common\DeptModel;
use App\Models\Common\UserModel;
//
use App\Models\Doc\Category as DocCategoryModel;
use App\Models\Doc\Files as DocFilesModel;
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
            $res['info']  = 'resource_word_error';
            return $this->fail($res);
        }

        switch ($resource) {
            case 'search_options':
                $result = $this->_reqSearchOptions();
                break;
            case 'blank_form':
                $result = $this->_repBlankForm();
                break;
            case 'list':
                $result = $this->_reqList();
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

    // 2023-3-17
    public function createOne()
    {
        if (!$this->validate('PartyBranchCreateOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client     = $this->request->getPost();
        $post_files = $this->request->getFiles();

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($client['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }
        //
        $uid     = $this->session->get('id');
        $IdMaker = new MyIdMaker();
        $uuid    = $IdMaker->apply('UUID');
        if ($uuid === false) {
            $res['info']  = 'uuid error';
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res, 500);
        }
        $record = [
            'station_id'       => $client['station_id'],
            'category_id'      => $client['category_id'],
            'title'            => $client['title'],
            'keywords'         => $client['keywords'],
            'summary'          => $client['summary'],
            'secret_level'     => $client['secret_level'],
            'retention_period' => $client['retention_period'],
            'store_place'      => $client['store_place'],
            'user_id'          => $uid,
            'uuid'             => $uuid,
        ];

        // 注意
        $record['status'] = 'published';

        // serial_id
        $category_id = (int) $client['category_id'];
        $model       = new PartyBranchModel();
        $serial_id   = $model->getLastSerialIdByCategory($category_id) + 1;

        // 类别编码
        $model2 = new DocCategoryModel();
        $fields = ['code'];
        $db     = $model2->getById($fields, (int) $client['category_id']);
        if (empty($db)) {
            $code = '0';
        } else {
            $code = $db['code'];
        }

        $record['serial_id'] = $serial_id;
        $record['doc_num']   = $client['station_id'] . '-' . $code . '-' . $serial_id;

        $result = $model->insertOneRecord($record);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // 文件
        $total = 0;
        if (!empty($post_files)) {
            $files = $post_files['files'];
            $total = count($files);
        }
        $num    = 0;
        $result = false;
        if ($total > 0) { // 保存文件，数据库记录文件信息
            $file_records = [];
            //
            foreach ($files as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                if ($file->getSize() > $this->config->maxFileSize) {
                    continue;
                }
                if ($file->getSize() === 0) {
                    continue;
                }
                $file_mime_type = $file->getMimeType();
                if (!in_array($file_mime_type, $this->config->partyBranch['allowedFileTypes'])) {
                    continue;
                }
                //
                $file_org_name = $file->getName();
                $file_org_name = str_replace(" ", "", $file_org_name);
                $file_new_name = $file->getRandomName();
                $file_ext      = $file->guessExtension();
                $size          = $file->getSize();
                //
                $path = WRITEPATH . $this->config->partyBranch['filePath'];
                if (!$file->hasMoved()) {
                    try {
                        $file->move($path, $file_new_name, true);
                    } catch (FileException $exception) {
                        // return $this->failServerError('保存文件出错');
                        continue;
                    }
                }
                //
                $file_records[] = [
                    'station_id'     => $client['station_id'],
                    'category_id'    => $client['category_id'],
                    'associated_id'  => $uuid,
                    'user_id'        => $uid,
                    'file_org_name'  => $file_org_name,
                    'file_new_name'  => $file_new_name,
                    'file_mime_type' => $file_mime_type,
                    'file_ext'       => $file_ext,
                    'size'           => $size,
                ];
                //
                $num++;
            }
            // 写数据库
            if ($num > 0) {
                $model3 = new DocFilesModel();
                $result = $model3->insertMultiRecords($file_records);
            }
        }
        //
        if ($total > 0) {
            if ($num > 0 && $result) {
                if ($num < $total) {
                    $res['msg'] = '新文档创建记录成功，但添加的文件有部分保存失败';
                    return $this->respond($res);
                }
            } else {
                $res['msg'] = '新文档创建记录成功，但添加的文件保存失败';
                return $this->respond($res);
            }
        }

        $res['msg'] = '新建完成';
        return $this->respond($res);
    }

    // 2023-3-30
    public function deleteFile()
    {
        if (!$this->validate('PartyBranchDeleteFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client        = $this->request->getJSON(true);
        $id            = $client['id'];
        $file_org_name = $client['file_org_name'];

        $fields = ['associated_id', 'file_org_name', 'file_new_name'];
        $model  = new DocFilesModel();
        $db     = $model->getByID($fields, $id);
        if (empty($db)) {
            return $this->failNotFound('文件已不存在');
        }
        if ($db['file_org_name'] !== $file_org_name) {
            return $this->failNotFound('找不到文件');
        }

        $result = $model->delByID($id);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        // Doc_store表记录更新日期和编辑人员
        $uuid = $db['associated_id'];
        $data = [
            'user_id' => $this->session->get('id'),
        ];
        $model2 = new PartyBranchModel();
        $result = $model2->updateRecordByUuid($data, $uuid);

        $path = WRITEPATH . $this->config->partyBranch['filePath'];
        $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $db['file_new_name'];
        if (file_exists($file)) {
            unlink($file);
        }

        $res['msg'] = '文件删除成功';
        return $this->respond($res);
    }

    // 2023-3-28
    public function downloadFile()
    {
        if (!$this->validate('PartyBranchDownloadFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $params        = $this->request->getGet();
        $id            = $params['id'];
        $file_org_name = $params['file_org_name'];

        $model  = new DocFilesModel();
        $fields = ['station_id', 'file_org_name', 'file_new_name', 'file_mime_type'];
        $db     = $model->getByID($fields, $id);

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

        $path = WRITEPATH . $this->config->partyBranch['filePath'];
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

    // 2023-3-30
    public function uploadFile()
    {
        if (!$this->validate('PartyBranchUploadFile')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $associated_id = $this->request->getPost('associated_id');
        $file          = $this->request->getFile('file');

        $model  = new PartyBranchModel();
        $fields = ['station_id', 'category_id'];
        $db     = $model->getRecordByUuid($fields, $associated_id);
        if (empty($db)) {
            return $this->failNotFound('无文档记录，不允许上传文件');
        }

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        if (empty($file)) {
            return $this->failServerError('文件上传失败');
        }

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
        $file_org_name  = str_replace(" ", "", $file_org_name);
        $file_new_name  = $file->getRandomName();
        $file_ext       = $file->guessExtension();
        $file_mime_type = $file->getMimeType();
        $size           = $file->getSize();

        $path = WRITEPATH . $this->config->partyBranch['filePath'];
        if (!$file->hasMoved()) {
            try {
                $file->move($path, $file_new_name, true);
            } catch (FileException $exception) {
                return $this->failServerError('保存文件出错');
            }
        }
        //
        $uid         = $this->session->get('id');
        $file_record = [
            'station_id'     => $db['station_id'],
            'category_id'    => $db['category_id'],
            'associated_id'  => $associated_id,
            'user_id'        => $uid,
            //
            'file_org_name'  => $file_org_name,
            'file_new_name'  => $file_new_name,
            'file_mime_type' => $file_mime_type,
            'file_ext'       => $file_ext,
            'size'           => $size,
        ];

        $model3 = new DocFilesModel();
        $id     = $model3->insertOneRecord($file_record);

        // Doc_store表记录更新日期和编辑人员
        $uuid = $associated_id;
        $data = [
            'user_id' => $this->session->get('id'),
        ];
        // $model = new PartyBranchModel();
        $result = $model->updateRecordByUuid($data, $uuid);

        $res['msg']  = '添加文档成功';
        $res['file'] = [
            'id'            => $id,
            'file_org_name' => $file_org_name,
            'file_ext'      => $file_ext,
        ];
        return $this->respond($res);
    }

    // 2023-4-22
    public function updateOne()
    {
        if (!$this->validate('PartyBranchUpdateOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);
        $uuid   = $client['uuid'];

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($client['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }
        //
        $model  = new PartyBranchModel();
        $fields = ['station_id', 'category_id'];
        $old    = $model->getRecordByUuid($fields, $uuid);
        if (empty($old)) {
            $res['error'] = '请求对象不存在';
            return $this->fail($res);
        }
        //
        $user_id = $this->session->get('id');
        $record  = [
            // 'station_id'  => $client['station_id'],
            'category_id'      => $client['category_id'],
            'title'            => $client['title'],
            'keywords'         => $client['keywords'],
            'secret_level'     => $client['secret_level'],
            'retention_period' => $client['retention_period'],
            'store_place'      => $client['store_place'],
            'summary'          => $client['summary'],
            'user_id'          => $user_id,
        ];

        //! 类别被修改，重新计算serial_id
        if ($client['category_id'] != $old['category_id']) {
            $category_id = (int) $client['category_id'];
            $serial_id   = $model->getLastSerialIdByCategory($category_id) + 1;

            // 类别编码
            $model2 = new DocCategoryModel();
            $fields = ['code'];
            $db     = $model2->getById($fields, $category_id);
            if (empty($db)) {
                $code = '0';
            } else {
                $code = $db['code'];
            }

            $record['serial_id'] = $serial_id;
            $record['doc_num']   = $client['station_id'] . '-' . $code . '-' . $serial_id;
        }

        $result = $model->updateRecordByUuid($record, $uuid);
        if ($result === false) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['msg'] = '修改完成';
        return $this->respond($res);
    }

    // 2023-4-22
    public function deleteOne()
    {
        if (!$this->validate('PartyBranchDeleteOne')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);
        $id     = $client['id'];
        $uuid   = $client['uuid'];
        $title  = $client['title'];

        $fields = ['uuid', 'station_id', 'title'];
        $model  = new PartyBranchModel();
        $db     = $model->getRecordById($fields, $id);
        if (empty($db)) {
            return $this->respond(['info' => 'empty']);
        }

        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($db['station_id'], $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        if ($uuid != $db['uuid'] || $title != $db['title']) {
            return $this->failNotFound('请求的数据不存在');
        }

        // 刷新记录的created_at
        $record = [
            'user_id' => $this->session->get('id'),
        ];
        $result = $model->updateRecordById($record, $id);
        // if (!$result) {
        //     return $this->failServerError('服务器处理发生错误，稍候再试');
        // }

        // 先删除已存储的文件
        $model2 = new DocFilesModel();
        $fields = ['file_new_name'];
        $db2    = $model2->getByAssociatedID($fields, $uuid);
        if (!empty($db2)) {
            $path = WRITEPATH . $this->config->partyBranch['filePath'];
            foreach ($db2 as $item) {
                $file = rtrim($path, '\\/ ') . DIRECTORY_SEPARATOR . $item['file_new_name'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        $result = $model2->deleteByAssociatedID($uuid);
        if (!$result) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $result = $model->delRecordById($id);
        if (!$result) {
            return $this->failServerError('服务器处理发生错误，稍候再试');
        }

        $res['msg'] = '完成删除';
        return $this->respond($res);
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
        //! 注意：只允许新增用户所属部门的数据
        $stationItems[] = $model->getDeptRecordById($fields, $station_id);

        //
        $model  = new DocCategoryModel();
        $fields = ['id'];
        $wheres = ['pid' => $station_id, 'alias' => 'PARTY_BRANCH'];
        $db     = $model->getByWheres($fields, $wheres);

        $categoryItems = [];
        if (!empty($db)) {
            $pid        = $db[0]['id'];
            $categories = $model->getAllChildren(['id', 'pid', 'name'], $pid);
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

    // 2023-3-19
    protected function _reqSearchOptions()
    {
        // 注意：只允许查看用户所属部门的数据
        $defaultStationId = $this->session->get('allowDefaultDeptId');
        if (empty($defaultStationId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $model        = new DeptModel();
        $fields       = ['id', 'name'];
        $stationItems = $model->getDeptRecordsByIds($fields, [$defaultStationId]);
        //
        $station_id = $defaultStationId;
        $model      = new DocCategoryModel();
        $fields     = ['id', 'pid', 'name'];
        $wheres     = ['pid' => $station_id, 'alias' => 'PARTY_BRANCH'];
        $db         = $model->getByWheres($fields, $wheres);

        $categoryItems = [];
        if (!empty($db)) {
            $pid        = $db[0]['id'];
            $categories = $model->getAllChildren(['id', 'pid', 'name'], $pid);
            helper('my_array');
            // $db[0]['children'] = my_arr2tree($categories);
            // $categoryItems     = [$db[0]];
            $categoryItems = my_arr2tree($categories);
        }

        $res['http_status'] = 200;
        $res['data']        = [
            'stationItems'  => $stationItems,
            'categoryItems' => $categoryItems,
        ];
        return $res;
    }

    // 2023-3-19
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

        $model      = new DocCategoryModel();
        $categories = [];
        $fields     = ['id', 'name'];
        $wheres     = ['pid' => $param['station_id'], 'alias' => 'PARTY_BRANCH'];
        $root       = $model->getByWheres($fields, $wheres);
        if (!empty($root)) {
            $pid        = $root[0]['id'];
            $categories = $model->getAllChildren(['id', 'name'], $pid, true);
        }
        if ($param['category_id'] === '0') {
            if (empty($categories)) {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'data' => []];
                return $res;
            }
            foreach ($categories as $value) {
                $query['category_id'][] = $value['id'];
            }
        } else {
            $temp = $model->getAllChildren(['id'], $param['category_id'], true);
            foreach ($temp as $value) {
                $query['category_id'][] = $value['id'];
            }
        }
        if (isset($param['title']) && $param['title'] !== '') {
            $query['title'] = $param['title'];
        }
        if (isset($param['keywords']) && $param['keywords'] !== '') {
            $query['keywords'] = $param['keywords'];
        }

        $query['limit']  = $param['limit'];
        $query['offset'] = $param['offset'];
        $fields          = ['id', 'uuid', 'title', 'doc_num', 'category_id', 'secret_level', 'retention_period', 'store_place', 'updated_at'];

        $model2 = new PartyBranchModel();
        $result = $model2->getRecordsByMultiConditions($fields, $query);

        if ($result['total'] === 0) {
            $res['http_status'] = 200;
            $res['data']        = ['total' => 0, 'data' => []];
            return $res;
        }
        //
        $cnt = count($result['data']);
        for ($i = 0; $i < $cnt; $i++) {
            foreach ($categories as $value) {
                if ($result['data'][$i]['category_id'] === $value['id']) {
                    $result['data'][$i]['category'] = $value['name'];
                }
            }
            $result['data'][$i]['secret_level']     = $this->_getNameMap($this->config->SECRET_LEVEL, $result['data'][$i]['secret_level']);
            $result['data'][$i]['retention_period'] = $this->_getNameMap($this->config->RETENTION_PERIOD, $result['data'][$i]['retention_period']);
        }

        $res['http_status'] = 200;
        $res['data']        = ['total' => $result['total'], 'data' => $result['data']];
        return $res;
    }

    // 2023-3-21
    protected function _reqDetails()
    {
        if (!$this->validate('PartBranchDetails')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params = $this->request->getGet();
        $uuid   = $params['uuid'];

        $fields = ['id', 'uuid', 'station_id', 'title', 'category_id', 'doc_num', 'keywords', 'summary', 'status', 'secret_level', 'retention_period', 'store_place', 'user_id', 'created_at', 'updated_at'];
        $model  = new PartyBranchModel();
        $db     = $model->getRecordByUuid($fields, $uuid);
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
        $details = [
            'id'               => $db['id'],
            'uuid'             => $db['uuid'],
            // 'station'          => '',
            'title'            => $db['title'],
            'category'         => '',
            'doc_num'          => $db['doc_num'],
            'keywords'         => $db['keywords'],
            'summary'          => $db['summary'],
            'secret_level'     => '',
            'retention_period' => '',
            'store_place'      => $db['store_place'],
            'username'         => '',
            'created_at'       => $db['created_at'],
            'updated_at'       => $db['updated_at'],
        ];

        // 显示名
        // $model  = new DeptModel();
        // $fields = ['name'];
        // $name   = $model->getDeptRecordById($fields, $db['station_id']);
        // if (!empty($name)) {
        //     $details['station'] = $name['name'];
        // }

        $model      = new DocCategoryModel();
        $fields     = ['id', 'pid', 'name'];
        $categories = $model->getAllParents($fields, $db['category_id']);
        $label      = '';
        if (!empty($categories)) {
            $length = count($categories);
            for ($i = 0; $i < $length; $i++) {
                $label = $label . $categories[$length - $i - 1]['name'] . ' / ';
            }
            $label = trim($label, ' / ');
        }
        $details['category'] = $label;

        $model    = new UserModel();
        $fields   = ['username'];
        $username = $model->getUserRecordById($fields, $db['user_id']);
        if (!empty($username)) {
            $details['username'] = $username['username'];
        }

        $details['secret_level']     = $this->_getNameMap($this->config->SECRET_LEVEL, $db['secret_level']);
        $details['retention_period'] = $this->_getNameMap($this->config->RETENTION_PERIOD, $db['retention_period']);

        // 查找文档附件
        $model  = new DocFilesModel();
        $fields = ['id', 'file_org_name', 'file_ext'];
        $files  = $model->getByAssociatedID($fields, $uuid);

        $res['http_status'] = 200;
        $res['data']        = [
            'details' => $details,
            'files'   => $files,
        ];
        return $res;
    }

    // 2023-4-21
    protected function _reqEdit()
    {
        if (!$this->validate('PartBranchEdit')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params = $this->request->getGet();
        $uuid   = $params['uuid'];

        $fields = ['id', 'uuid', 'station_id', 'title', 'category_id', 'doc_num', 'keywords', 'summary', 'status', 'secret_level', 'retention_period', 'store_place', 'user_id', 'created_at', 'updated_at'];
        $model  = new PartyBranchModel();
        $db     = $model->getRecordByUuid($fields, $uuid);
        if (empty($db)) {
            $res['http_status'] = 404;
            $res['msg']         = '没有找到请求的数据';
            return $res;
        }

        $station_id       = $db['station_id'];
        $allowWriteDeptId = $this->session->get('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }
        // $record = $db;
        $record = [
            'id'               => $db['id'],
            'uuid'             => $db['uuid'],
            'station_id'       => $db['station_id'],
            'title'            => $db['title'],
            'category_id'      => $db['category_id'],
            'category'         => [],
            // 'doc_num'          => $db['doc_num'],
            'keywords'         => $db['keywords'],
            'summary'          => $db['summary'],
            'secret_level'     => $db['secret_level'],
            'retention_period' => $db['retention_period'],
            'store_place'      => $db['store_place'],
            // 'username'         => '',
            // 'created_at'       => $db['created_at'],
            // 'updated_at'       => $db['updated_at'],
        ];

        $model        = new DeptModel();
        $fields       = ['id', 'name', 'alias'];
        $stationItems = $model->getDeptRecordsByIds($fields, $allowWriteDeptId);

        $model  = new DocCategoryModel();
        $fields = ['id'];
        $wheres = ['pid' => $station_id, 'alias' => 'PARTY_BRANCH'];
        $db     = $model->getByWheres($fields, $wheres);

        $categoryItems = [];
        if (!empty($db)) {
            $pid        = $db[0]['id'];
            $categories = $model->getAllChildren(['id', 'pid', 'name'], $pid);
            helper('my_array');
            $categoryItems = my_arr2tree($categories);
        }
        //
        $fields = ['id'];
        $db     = $model->getAllParents($fields, $record['category_id'], $pid);
        if (!empty($db)) {
            $cnt = count($db);
            for ($i = 0; $i < $cnt; $i++) {
                $record['category'][] = $db[$cnt - 1 - $i]['id'];
            }
        }
        //
        $secretLevelItems     = $this->config->SECRET_LEVEL;
        $retentionPeriodItems = $this->config->RETENTION_PERIOD;

        // 查找文档附件
        $model  = new DocFilesModel();
        $fields = ['id', 'file_org_name', 'file_ext'];
        $files  = $model->getByAssociatedID($fields, $uuid);

        $res['http_status'] = 200;
        $res['data']        = [
            'record'               => $record,
            'stationItems'         => $stationItems,
            'categoryItems'        => $categoryItems,
            'secretLevelItems'     => $secretLevelItems,
            'retentionPeriodItems' => $retentionPeriodItems,
            'files'                => $files,
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
