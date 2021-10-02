<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-02 22:26:50
 */

namespace App\Controllers;

use App\Models\Dts\TicketModel;
use App\MyEntity\Workflow\Dts\Ticket;
use App\MyEntity\Workflow\Dts\WorkflowCore;
use CodeIgniter\API\ResponseTrait;

class Dts extends BaseController
{
    use ResponseTrait;

    // 进展模板
    protected $progressTemplate;
    // 流程节点名
    protected $workflowPlaces;
    // 单号末尾几位
    protected $ticketIdTailStartAt;

    public function __construct()
    {
        $this->progressTemplate = [
            'draft' => "\n【问题描述】\n\n【发生时间】\n\n【问题影响】\n\n【已采取措施】\n\n",
        ];

        $this->workflowPlaces = [
            'post'    => 'post',
            'check'   => 'check',
            'review'  => 'review',
            'resolve' => 'resolve',
            'close'   => 'close',
            'suspend' => 'suspend',
            'reject'  => 'reject',
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

        $station_id = $params['station_id'];
        $place      = $params['place'];

        $ticket   = new Ticket($this->workflowPlaces[$place]);
        $wf       = new WorkflowCore($ticket);
        $metadata = $wf->getPlaceMetadata($this->workflowPlaces[$place]);
        $assignTo = $metadata['assignTo'];

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '测试...';
        // $res['data'] = ['total' => $result['total'], 'data' => $result['result']];

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
            'creator'        => session('username'),
        ];

        $ticket = new Ticket($this->workflowPlaces['check']);
        $wf     = new WorkflowCore($ticket);
        $wf->toReject();

        // 单号
        $time = time();

        $columnName = ['ticket_id'];
        $query      = ['created_at' => date('Y-m-d', $time)];
        $db         = $model->countByCreateDate($columnName, $query);

        $temp               = (string) ($this->ticketIdTailStartAt + $db);
        $draft['ticket_id'] = date('Ymd', $time) . substr($temp, 1);
        // 工作流
        $draft['place_at'] = $this->workflowPlaces['check'];
        $metadata          = $wf->getPlaceMetadata($this->workflowPlaces['check']);
        $draft['handler']  = $metadata['handler'];

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
        $ticket = new Ticket($this->workflowPlaces['post']);
        $wf     = new WorkflowCore($ticket);
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
