<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-04 21:42:44
 */

namespace App\Controllers;

use App\Models\Dts\RoleWorkflowAuthorityModel;
use App\Models\Dts\TicketModel;
use App\Models\Dts\UserModel;
use App\Models\Dts\UserRoleModel;
use App\Models\Dts\WorkflowAuthorityModel;
use App\MyEntity\Workflow\Dts\WorkflowCore;
use CodeIgniter\API\ResponseTrait;

class Dts extends BaseController
{
    use ResponseTrait;

    // 进展模板
    protected $progressTemplate;
    // 单号末尾几位
    protected $ticketIdTailStartAt;

    public function __construct()
    {
        $this->progressTemplate = [
            'draft' => "\n【问题描述】\n\n【发生时间】\n\n【问题影响】\n\n【已采取措施】\n\n",
        ];

        $this->ticketIdTailStartAt = 1001;
    }

    public function getProgressTemplate()
    {
        $text = date('Y-m-d H:i', time()) . ' ' . session('username') . $this->progressTemplate['draft'];

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = $text;

        return $this->respond($res);
    }

    public function getHandler()
    {
        $params = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('DtsGetHandler')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $station_id = $params['station_id'];
        $place      = $params['place'];

        $wf       = new WorkflowCore();
        $metadata = $wf->getPlaceMetadata($place);
        if (!isset($metadata['assignTo'])) {
            $res['code'] = EXIT_ERROR;
            return $this->respond($res);
        }

        // 获取流程权限
        $model      = new WorkflowAuthorityModel();
        $columnName = ['id'];
        $query      = ['alias' => $metadata['assignTo']];
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

        // 过滤用户
        $user = [];
        $cnt  = count($db);
        $find = '+' . $station_id . '+';
        for ($i = 0; $i < $cnt; $i++) {
            if (strpos($db[$i]['dept_ids'], $find) !== false) {
                $user[] = [
                    'id'       => $db[$i]['id'],
                    'username' => $db[$i]['username'],
                    'status'   => $db[$i]['status'],
                ];
            }
        }

        if (!empty($user)) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = $user;

        } else {
            $res['code'] = EXIT_ERROR;
        }

        return $this->respond($res);
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
        $draft = [
            'station_id'     => $client['station_id'],
            'type'           => $client['type'],
            'title'          => $client['title'],
            'level'          => $client['level'],
            'equipment_unit' => $client['equipment_unit'],
            'progress'       => $client['progress'],
            'handler'        => $client['handler'],
            'creator'        => session('username'),
        ];

        // 单号
        $time               = time();
        $columnName         = ['ticket_id'];
        $query              = ['created_at' => date('Y-m-d', $time)];
        $db                 = $model->countByCreateDate($columnName, $query);
        $temp               = (string) ($this->ticketIdTailStartAt + $db);
        $draft['ticket_id'] = date('Ymd', $time) . substr($temp, 1);

        // 工作流
        $place             = 'check';
        $draft['place_at'] = $place;

        $result = $model->newDraft($draft);
        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已创建新问题单';

        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getForList()
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

        $columnName = ['id', 'ticket_id', 'type', 'title', 'level', 'place_at', 'created_at', 'updated_at', 'creator', 'handler'];
        $db         = $model->getByLimitOffset($columnName, $query);

        // 工作流
        // $place  = 'post';
        // $ticket = new Ticket($place);
        $wf = new WorkflowCore();
        for ($i = 0; $i < $db['total']; $i++) {
            $metadata = $wf->getPlaceMetadata($db['data'][$i]['place_at']);
            if (!empty($metadata)) {
                $db['data'][$i]['place_at'] = $metadata['name'];
            }
        }

        if ($db) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['total' => $db['total'], 'data' => $db['data']];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '稍后再试';
        }

        return $this->respond($res);
    }

    public function getTicketDetails()
    {

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '测试...';

        return $this->respond($res);
    }

}
