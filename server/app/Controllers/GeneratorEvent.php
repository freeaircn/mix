<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-14 10:25:25
 */

namespace App\Controllers;

use App\Models\Generator\GenEventLogModel;
use CodeIgniter\API\ResponseTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class GeneratorEvent extends BaseController
{
    use ResponseTrait;

    // 发电机编号
    protected $firstGenId;
    protected $lastGenId;
    protected $genTotalNumber;
    // 事件类型编码
    protected $eventStart;
    protected $eventStop;
    //
    protected $eventModel;

    public function __construct()
    {
        $this->firstGenId     = 1;
        $this->lastGenId      = 3;
        $this->genTotalNumber = 3;
        //
        $this->eventStop  = '1';
        $this->eventStart = '2';
        //
        $this->eventModel = new GenEventLogModel();
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

        // 取出检验后的数据
        $station_id   = $client['station_id'];
        $generator_id = $client['generator_id'];
        $newEvent     = [
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
        ];

        // 查找时间最近一条事件
        $lastEvent = $this->eventModel->getLastLogByStationGen($station_id, $generator_id);

        // 检查时间，事件的合理性
        $validateMsg = $this->validateNewEvent($lastEvent, $newEvent);
        if ($validateMsg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $validateMsg;
            return $this->respond($res);
        }

        // 新事件处理
        // $result = $this->newEventProcess($lastEvent, $newEvent);

        // 添加 DB
        $result = $this->eventModel->newEventLog($newEvent);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '添加一条新记录';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
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

        $utils = service('mixUtils');
        $start = $utils->getFirstDayOfMonth($param['date']);
        $end   = $utils->getLastDayOfMonth($param['date']);

        $query = [
            'station_id'   => $param['station_id'],
            'generator_id' => $param['generator_id'],
            'start'        => $start,
            'end'          => $end,
            'limit'        => $param['limit'],
            'offset'       => $param['offset'],
        ];

        $columnName = ['id', 'station_id', 'generator_id', 'event', 'event_at', 'creator', 'description'];
        $result     = $this->eventModel->getLogsForShowList($columnName, $query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['total' => $result['total'], 'data' => $result['result']];

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

        // 取出检验后的数据
        $id           = $client['id'];
        $station_id   = $client['station_id'];
        $generator_id = $client['generator_id'];
        $newEvent     = [
            'id'           => $client['id'],
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
        ];

        // 查找时间最近
        $db = $this->eventModel->getLastLogByStationGen($station_id, $generator_id, 2);

        // 对比
        if ($db[0]['id'] != $id) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '只能修改某机组时间最近的一条记录';
            return $this->respond($res);
        }

        if ($db[0]['event'] != $client['event']) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid event';
            return $this->respond($res);
        }

        // 检查时间戳，事件的合理性
        $validateMsg = $this->validateNewEvent($db[1], $newEvent);
        if ($validateMsg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $validateMsg;
            return $this->respond($res);
        }

        // 修改记录
        $result = $this->eventModel->saveEventLog($newEvent);

        // if ($client['event'] == '1') {
        //     $result = $this->updateEventGenStop($db[1], $newEvent);
        // } else if ($client['event'] == '2') {
        //     $result = $this->eventModel->saveEventLog($newEvent);
        // }

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

        // 查找时间最近
        $db = $this->eventModel->getLastLogByStationGen($client['station_id'], $client['generator_id']);

        // 对比
        if ($db['id'] != $client['id']) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '只能删除某机组时间最近的一条记录';
            return $this->respond($res);
        }

        if ($db['event'] != $client['event']) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid event';
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

        // 查询数据
        $columnName = ['event', 'event_at', 'creator', 'description'];
        $query      = [
            'station_id'   => $param['station_id'],
            'year'         => substr($param['date'], 0, 4),
            'generator_id' => 0,
        ];

        $spreadsheet = new Spreadsheet();
        $sheetTitle  = ['序号', '机组', '事件', '时间', '记录人', '说明'];

        for ($GID = 1; $GID <= $this->genTotalNumber; $GID++) {
            $query['generator_id'] = $GID;

            $db  = $this->eventModel->getByStationDateGen($columnName, $query);
            $cnt = count($db);
            if ($cnt > 0) {
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
                foreach ($db as $item) {
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

        // 初值
        $res = [];
        for ($i = 0; $i < $this->genTotalNumber; $i++) {
            $res[] = [
                'gid'          => ($i + 1) . 'G',
                'start_num'    => 0,
                'running_time' => 0,
                'last_at'      => 'NULL',
            ];
        }

        $utils = service('mixUtils');

        // 查询记录
        $columnName = ['event', 'event_at'];
        $query      = [
            'year'         => $param['year'],
            'station_id'   => $param['station_id'],
            'generator_id' => 0,
        ];
        for ($i = 0; $i < $this->genTotalNumber; $i++) {
            $query['generator_id'] = ($i + 1);

            $db = $this->eventModel->getByStationDateGen($columnName, $query);
            if (!empty($db)) {
                $cnt          = count($db);
                $start_num    = 0;
                $running_time = 0;
                $start_at     = '';
                for ($j = 0; $j < $cnt; $j++) {
                    if ($db[$j]['event'] == $this->eventStart) {
                        $start_num += 1;
                        $start_at = $db[$j]['event_at'];
                        continue;
                    }
                    if ($j === 0 && $db[$j]['event'] == $this->eventStop) {
                        $firstDayOfYear = $utils->getFirstDayOfYear($db[$j]['event_at'], 0, true);
                        $running_time += (strtotime($db[$j]['event_at']) - strtotime($firstDayOfYear));
                        continue;
                    }
                    if ($j !== 0 && $db[$j]['event'] == $this->eventStop) {
                        $running_time += (strtotime($db[$j]['event_at']) - strtotime($start_at));
                        continue;
                    }
                }
                $res[$i]['start_num']    = $start_num;
                $res[$i]['running_time'] = round($running_time / 3600, 2);
                $res[$i]['last_at']      = $db[$cnt - 1]['event_at'];
            }
        }

        $response['code'] = EXIT_SUCCESS;
        $response['data'] = $res;

        return $this->respond($response);
    }

    // 内部方法
    protected function validateNewEvent(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        $now = time();
        if (!empty($lastEvent)) {
            // 检查时间戳
            $lastEventAt = strtotime($lastEvent['event_at']);
            $newEventAt  = strtotime($newEvent['event_at']);
            if ($newEventAt <= $lastEventAt || $newEventAt > $now) {
                return '检查选择的日期、时间';
            }

            if ($lastEvent['event'] == $newEvent['event']) {
                return '与保存的记录冲突';
            }
        } else {
            // 检查时间戳
            $newEventAt = strtotime($newEvent['event_at']);
            if ($newEventAt > $now) {
                return '检查选择的日期、时间';
            }
        }

        return true;
    }

    // protected function newEventProcess(array $lastEvent = [], array $newEvent = [])
    // {
    //     if (empty($newEvent)) {
    //         return false;
    //     }

    //     $result = false;
    //     switch ($newEvent['event']) {
    //         // 事件：停机
    //         case '1':
    //             $result = $this->newEventGenStop($lastEvent, $newEvent);
    //             break;
    //         // 事件：开机
    //         case '2':
    //             $result = $this->newEventGenStart($newEvent);
    //             break;
    //         default:
    //             return false;
    //             break;
    //     }
    //     return $result;
    // }

    // protected function newEventGenStart(array $newEvent = [])
    // {
    //     if (empty($newEvent)) {
    //         return false;
    //     }

    //     // 添加事件
    //     $eventId = $this->eventModel->newEventLog($newEvent);
    //     if ($eventId === false) {
    //         return false;
    //     }
    //     return true;
    // }

    // protected function newEventGenStop(array $lastEvent = [], array $newEvent = [])
    // {
    //     if (empty($newEvent)) {
    //         return false;
    //     }

    //     if (!empty($lastEvent)) {
    //         $lastEventYear = substr($lastEvent['event_at'], 0, 4);
    //         $newEventYear  = substr($newEvent['event_at'], 0, 4);

    //         $lastEventAt = strtotime($lastEvent['event_at']);
    //         $newEventAt  = strtotime($newEvent['event_at']);

    //         if ($lastEventYear === $newEventYear) {
    //             // 计算时长
    //             $newEvent['run_time'] = $newEventAt - $lastEventAt;
    //             // 添加事件
    //             $eventId = $this->eventModel->newEventLog($newEvent);
    //             if ($eventId === false) {
    //                 return false;
    //             }
    //         } else if (($lastEventYear + 1) == $newEventYear) {
    //             // 计算时长
    //             $firstDay = mktime(0, 0, 0, 1, 1, $newEventYear);
    //             // 事件：X年
    //             $lastEvent['run_time'] = $firstDay - $lastEventAt;
    //             // 事件：X+1年
    //             $newEvent['run_time'] = $newEventAt - $firstDay;

    //             $this->eventModel->transStart();
    //             $this->eventModel->newEventLog($newEvent);
    //             $this->eventModel->saveEventLog($lastEvent);
    //             $this->eventModel->transComplete();
    //             if ($this->eventModel->transStatus() === false) {
    //                 return false;
    //             }
    //         }
    //     } else {
    //         // 添加事件
    //         $eventId = $this->eventModel->newEventLog($newEvent);
    //         if ($eventId === false) {
    //             return false;
    //         }
    //     }

    //     return true;
    // }

    // protected function updateEventGenStop(array $lastEvent = [], array $newEvent = [])
    // {
    //     if (empty($newEvent)) {
    //         return false;
    //     }

    //     if (!empty($lastEvent)) {
    //         $lastEventYear = substr($lastEvent['event_at'], 0, 4);
    //         $newEventYear  = substr($newEvent['event_at'], 0, 4);

    //         $lastEventAt = strtotime($lastEvent['event_at']);
    //         $newEventAt  = strtotime($newEvent['event_at']);

    //         if ($lastEventYear === $newEventYear) {
    //             // 计算时长
    //             $newEvent['run_time'] = $newEventAt - $lastEventAt;
    //         } else if (($lastEventYear + 1) == $newEventYear) {
    //             // 计算时长
    //             $firstDay = mktime(0, 0, 0, 1, 1, $newEventYear);
    //             // 事件：X+1年
    //             $newEvent['run_time'] = $newEventAt - $firstDay;
    //         }
    //     }

    //     // 修改事件
    //     return $this->eventModel->saveEventLog($newEvent);
    // }
}
