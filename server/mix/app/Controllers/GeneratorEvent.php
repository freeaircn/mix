<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-24 10:14:11
 */

namespace App\Controllers;

use App\Models\Generator\GenEventLogModel;
use CodeIgniter\API\ResponseTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GeneratorEvent extends BaseController
{
    use ResponseTrait;

    protected $eventModel;

    public function __construct()
    {
        $this->eventModel = new GenEventLogModel();
    }

    public function getGeneratorEvent()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('GeneratorEventGet')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($param['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        $query['station_id']   = $param['station_id'];
        $query['generator_id'] = $param['generator_id'];
        $query['start']        = $param['start'];
        $query['end']          = $param['end'];
        $query['limit']        = $param['limit'];
        $query['offset']       = $param['offset'];

        $result = $this->eventModel->getEventLog([], $query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['total' => $result['total'], 'data' => $result['result']];

        return $this->respond($res);
    }

    public function newGeneratorEvent()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventNew')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 取出检验后的数据
        $newEvent = [
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
        ];

        // 查找时间最近一条事件
        $lastEvent = $this->eventModel->getLastEventLogByStationGen($client['station_id'], $client['generator_id']);

        // 检查时间戳，事件的合理性
        $validMsg = $this->validNewEvent($lastEvent, $newEvent);
        if ($validMsg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $validMsg;
            return $this->respond($res);
        }

        // 新事件处理
        $result = $this->newEventProcess($lastEvent, $newEvent);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '添加一条新记录';
            // $res['data'] = ['id' => $result];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateGeneratorEvent()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventUpdate')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 取出检验后的数据
        $newEvent = [
            'id'           => $client['id'],
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
        ];

        // 查找时间最近一条事件
        $lastEvents = $this->eventModel->getLastEventLogByStationGen($client['station_id'], $client['generator_id'], 2);

        // 对比
        if ($lastEvents[0]['id'] != $client['id']) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '只能修改某机组最新的一条记录';
            return $this->respond($res);
        }

        if (($lastEvents[0]['station_id'] != $client['station_id']) || ($lastEvents[0]['generator_id'] != $client['generator_id']) || ($lastEvents[0]['event'] != $client['event'])) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid params';
            return $this->respond($res);
        }

        // 检查时间戳，事件的合理性
        $validMsg = $this->validNewEvent($lastEvents[1], $newEvent);
        if ($validMsg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $validMsg;
            return $this->respond($res);
        }

        // 修改记录
        if ($client['event'] == '1') {
            $result = $this->updateEventGenStop($lastEvents[1], $newEvent);
        } else if ($client['event'] == '2') {
            $result = $this->eventModel->saveEventLog($newEvent);
        }
        // $result = $this->newEventProcess($lastEvent, $newEvent);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改一条记录';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function delGeneratorEvent()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventDelete')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid station_id';
            return $this->respond($res);
        }

        // 查找时间最进一条事件
        $lastEvent = $this->eventModel->getLastEventLogByStationGen($client['station_id'], $client['generator_id']);

        // 对比
        if ($lastEvent['id'] != $client['id']) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '只能删除某机组最新的一条记录';
            return $this->respond($res);
        }

        if (($lastEvent['station_id'] != $client['station_id']) || ($lastEvent['generator_id'] != $client['generator_id']) || ($lastEvent['event'] != $client['event'])) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid params';
            return $this->respond($res);
        }

        $result = $this->eventModel->delEventLogById($client['id']);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 导出excel文件
    public function getExportGeneratorEvent()
    {
        $param = $this->request->getGet();

        // 检查输入
        if (!$this->validate('GeneratorEventExport')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($param['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        //
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($spreadsheet);
        $writer->save('hello world.xlsx');

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $param];

        return $this->respond($res);
    }

    public function getGeneratorEventStatistic()
    {
        $param = $this->request->getGet();

        // 检查输入
        if (empty($param) || !isset($param['station_id']) || !isset($param['year'])) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }
        if (!is_numeric($param['station_id']) || !is_numeric($param['year'])) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query['year']       = $param['year'];
        $query['station_id'] = $param['station_id'];

        $result = $this->eventModel->getStatisticByYearAndStation($query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $result];

        return $this->respond($res);
    }

    // 内部方法
    protected function validNewEvent(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        $now = time();
        if (!empty($lastEvent)) {
            // 检查时间戳
            $lastEventAt = strtotime($lastEvent['event_at']);
            $newEventAt  = strtotime($newEvent['event_at']);
            if ($newEventAt < $lastEventAt || $newEventAt > $now) {
                return '请检查填写的日期、时间';
            }

            if (($lastEvent['event'] === '1') && ($newEvent['event'] === '1')) {
                return '最后一条记录是停机，请检查填写的事件';
            }
            if (($lastEvent['event'] === '2') && ($newEvent['event'] === '2')) {
                return '最后一条记录是开机，请检查填写的事件';
            }
        } else {
            // 检查时间戳
            $newEventAt = strtotime($newEvent['event_at']);
            if ($newEventAt > $now) {
                return '请检查填写的日期、时间';
            }
        }

        return true;
    }

    protected function newEventProcess(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        $result = false;
        switch ($newEvent['event']) {
            // 事件：停机
            case '1':
                $result = $this->newEventGenStop($lastEvent, $newEvent);
                break;
            // 事件：开机
            case '2':
                $result = $this->newEventGenStart($newEvent);
                break;
            default:
                return false;
                break;
        }
        return $result;
    }

    protected function newEventGenStart(array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        // 添加事件
        $eventId = $this->eventModel->newEventLog($newEvent);
        if ($eventId === false) {
            return false;
        }
        return true;
    }

    protected function newEventGenStop(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        if (!empty($lastEvent)) {
            $lastEventYear = substr($lastEvent['event_at'], 0, 4);
            $newEventYear  = substr($newEvent['event_at'], 0, 4);

            $lastEventAt = strtotime($lastEvent['event_at']);
            $newEventAt  = strtotime($newEvent['event_at']);

            if ($lastEventYear === $newEventYear) {
                // 计算时长
                $newEvent['run_time'] = $newEventAt - $lastEventAt;
                // 添加事件
                $eventId = $this->eventModel->newEventLog($newEvent);
                if ($eventId === false) {
                    return false;
                }
            } else if (($lastEventYear + 1) == $newEventYear) {
                // 计算时长
                $firstDay = mktime(0, 0, 0, 1, 1, $newEventYear);
                // 事件：X年
                $lastEvent['run_time'] = $firstDay - $lastEventAt;
                // 事件：X+1年
                $newEvent['run_time'] = $newEventAt - $firstDay;

                $this->eventModel->transStart();
                $this->eventModel->newEventLog($newEvent);
                $this->eventModel->saveEventLog($lastEvent);
                $this->eventModel->transComplete();
                if ($this->eventModel->transStatus() === false) {
                    return false;
                }
            }
        } else {
            // 添加事件
            $eventId = $this->eventModel->newEventLog($newEvent);
            if ($eventId === false) {
                return false;
            }
        }

        return true;
    }

    protected function updateEventGenStop(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        if (!empty($lastEvent)) {
            $lastEventYear = substr($lastEvent['event_at'], 0, 4);
            $newEventYear  = substr($newEvent['event_at'], 0, 4);

            $lastEventAt = strtotime($lastEvent['event_at']);
            $newEventAt  = strtotime($newEvent['event_at']);

            if ($lastEventYear === $newEventYear) {
                // 计算时长
                $newEvent['run_time'] = $newEventAt - $lastEventAt;
            } else if (($lastEventYear + 1) == $newEventYear) {
                // 计算时长
                $firstDay = mktime(0, 0, 0, 1, 1, $newEventYear);
                // 事件：X+1年
                $newEvent['run_time'] = $newEventAt - $firstDay;
            }
        }

        // 修改事件
        return $this->eventModel->saveEventLog($newEvent);
    }
}
