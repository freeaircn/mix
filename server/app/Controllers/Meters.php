<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-08-28 23:00:32
 */

namespace App\Controllers;

use App\Models\Meter\MeterLogModel;
use CodeIgniter\API\ResponseTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Meters extends BaseController
{
    use ResponseTrait;

    protected $meterLogModel;

    public function __construct()
    {
        $this->meterLogModel = new MeterLogModel();
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

    public function newMeterLogs()
    {
        // 检查请求数据
        if (!$this->validate('MeterLogsNew')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 检查数据
        $valid = true;
        if (isset($client['meter'])) {
            for ($i = 0; $i < count($client['meter']); $i++) {
                foreach ($client['meter'][$i] as $value) {
                    if (!is_numeric($value)) {
                        $valid = false;
                        break;
                    }
                }
            }
        } else {
            $valid = false;
        }

        if (!$valid) {
            $res['error'] = 'invalid meter value';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 取出检验后的数据
        $meters = $client['meter'];
        if (date("H") > 18 && date("H") < 21) {
            $log_at = $client['log_at'] . ' 20:00:00';
        } else {
            $log_at = $client['log_at'] . ' 23:55:00';
        }
        $others = [
            'station_id' => $client['station_id'],
            'log_at'     => $log_at,
            'creator'    => $client['creator'],
        ];

        // 查找末尾的9条记录，对应9块电度表
        $limit    = 9;
        $lastLogs = $this->meterLogModel->getLastLogsByStation($client['station_id'], $limit);

        // 首次记录
        if (count($lastLogs) === 0) {
            $result = $this->meterLogModel->batchInsert($meters, $others);

            if ($result) {
                $res['code'] = EXIT_SUCCESS;
                $res['msg']  = '添加新记录';
            } else {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '添加失败，稍后再试';
            }
            return $this->respond($res);
        }

        if (count($lastLogs) < 9) {
            $res['error'] = 'last log num < 9';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请再尝试提交';
            return $this->respond($res);
        }

        // 比较日期/时间
        if ($lastLogs[0]['log_at'] === $log_at) {
            $res['error'] = 'log_at conflict';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '输入的日期已存在记录';
            return $this->respond($res);
        }

        // $validMsg = $this->validNewEvent($lastEvent, $newEvent);
        // if ($validMsg !== true) {
        //     $res['code'] = EXIT_ERROR;
        //     $res['msg']  = $validMsg;
        //     return $this->respond($res);
        // }

        // // 新事件处理
        // $result = $this->newEventProcess($lastEvent, $newEvent);

        // if ($result) {
        //     $res['code'] = EXIT_SUCCESS;
        //     $res['msg']  = '添加一条新记录';
        //     // $res['data'] = ['id' => $result];
        // } else {
        //     $res['code'] = EXIT_ERROR;
        //     $res['msg']  = '添加失败，稍后再试';
        // }

        $res['code'] = EXIT_SUCCESS;
        $res['msg']  = '添加一条新记录';
        $res['data'] = $lastLogs;
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

        // 查询数据
        $query['station_id'] = $param['station_id'];
        $query['start']      = $param['start'];
        $query['end']        = $param['end'];

        $spreadsheet = new Spreadsheet();
        $sheetTitle  = ['序号', '机组', '事件', '时间', '记录人', '说明'];

        for ($GID = 1; $GID < 4; $GID++) {
            $query['generator_id'] = $GID;

            $result = $this->eventModel->getEventLog([], $query);
            if ($result['total'] > 0) {
                $sheet  = $spreadsheet->createSheet($GID - 1)->setTitle($GID . 'G');
                $column = 1;
                $row    = 1;
                // 表头
                foreach ($sheetTitle as $key => $value) {
                    $sheet->setCellValueByColumnAndRow($key + $column, $row, $value);
                }
                // 样式
                $style = [
                    'font'      => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];
                $sheet->getStyle('A1:F1')->applyFromArray($style);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setWidth(35);
                // 填写数据
                $row = 2;
                foreach ($result['result'] as $item) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $row - 1);
                    $sheet->setCellValueByColumnAndRow(2, $row, $GID . 'G');
                    if ($item['event'] == '1') {
                        $sheet->setCellValueByColumnAndRow(3, $row, '停机');
                    }
                    if ($item['event'] == '2') {
                        $sheet->setCellValueByColumnAndRow(3, $row, '开机');
                    }
                    $sheet->setCellValueByColumnAndRow(4, $row, $item['event_at']);
                    $sheet->setCellValueByColumnAndRow(5, $row, $item['creator']);
                    $sheet->setCellValueByColumnAndRow(6, $row, $item['description']);
                    $row++;
                }
                // 样式
                $style['font']['bold'] = false;
                $sheet->getStyle('A2:F' . $row)->applyFromArray($style);
            } else {
                continue;
            }
        }
        $spreadsheet->setActiveSheetIndex(0);
        $sheetCount = $spreadsheet->getSheetCount();
        if ($sheetCount > 1) {
            $spreadsheet->removeSheetByIndex($sheetCount - 1);
        }

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="download.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        // header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        // header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        // header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        // header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        // header('Pragma: public'); // HTTP/1.0

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

        // $res['code'] = EXIT_SUCCESS;
        // $res['data'] = ['data' => $param];

        // return $this->respond($res);
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
