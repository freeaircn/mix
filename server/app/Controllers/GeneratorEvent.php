<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-28 20:04:15
 */

namespace App\Controllers;

use App\Models\Generator\Event\RecordModel;
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

    public function __construct()
    {
        $this->firstGenId     = 1;
        $this->lastGenId      = 3;
        $this->genTotalNumber = 3;
        //
        $this->eventStop             = '1';
        $this->eventStart            = '2';
        $this->eventMaintenanceStart = '3';
        $this->eventMaintenanceStop  = '4';

        helper('my_date');
    }

    public function newRecord()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventNewRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 取出检验后的数据
        $station_id   = $client['station_id'];
        $generator_id = $client['generator_id'];
        $event        = $client['event'];
        $newEvent     = [
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'cause'        => $client['cause'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
            'description'  => $client['description'],
        ];

        $model = new RecordModel();

        // 预处理: start stop事件
        if ($event == $this->eventStop || $event == $this->eventStart) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $newEvent['station_id'],
                'generator_id' => $newEvent['generator_id'],
                'events'       => [$this->eventStop, $this->eventStart],
            ];
        }
        if ($event == $this->eventMaintenanceStart || $event == $this->eventMaintenanceStop) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $newEvent['station_id'],
                'generator_id' => $newEvent['generator_id'],
                'events'       => [$this->eventMaintenanceStart, $this->eventMaintenanceStop],
            ];
        }
        $lastEvent = $model->getLastByStationGIdEvents([], $query);
        $msg       = $this->validateNewEvent($lastEvent, $newEvent);
        if ($msg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $msg;
            return $this->respond($res);
        }

        // 添加
        $db = $model->insertRecord($newEvent);

        if ($db) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '添加成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getRecord()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('GeneratorGetEvent')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query = [
            'station_id'   => $param['station_id'],
            'generator_id' => $param['generator_id'],
            'event'        => $param['event'],
            'description'  => $param['description'],
            'limit'        => $param['limit'],
            'offset'       => $param['offset'],
        ];

        $date  = date('Y-m-d', time());
        $model = new RecordModel();

        // 预处理-查询日期
        if (empty($param['date'])) {
            $query2     = ['station_id' => $query['station_id']];
            $columnName = ['event_at'];
            $db         = $model->getLastDateByStation($columnName, $query2);
            if (!isset($db['event_at'])) {
                $res['code'] = EXIT_SUCCESS;
                $res['data'] = ['total' => 0, 'date' => $date, 'data' => []];

                return $this->respond($res);
            }
            $date = $db['event_at'];
        } else {
            $regexDate = '/^20\d{2}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])$/';

            if (preg_match($regexDate, $param['date']) === 0) {
                $res['error'] = 'invalid date';

                $res['code'] = EXIT_ERROR;
                $res['msg']  = '请求数据无效';
                return $this->respond($res);
            }
            $date = $param['date'];
        }

        $query['start'] = my_first_day_of_month($date);
        $query['end']   = my_last_day_of_month($date);

        $columnName = ['id', 'station_id', 'generator_id', 'event', 'cause', 'event_at', 'creator', 'description'];
        $db         = $model->getByStationGIdDateRange($columnName, $query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['total' => $db['total'], 'date' => date('Y-m-d', strtotime($date)), 'data' => $db['result']];

        return $this->respond($res);
    }

    public function delRecord()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventDelRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        $id           = $client['id'];
        $station_id   = $client['station_id'];
        $generator_id = $client['generator_id'];
        $event        = $client['event'];

        $model = new RecordModel();
        // 查找时间最近
        // $db = $model->getLastRecordByStationGId($client['station_id'], $client['generator_id']);

        // 预处理: start stop事件
        if ($event == $this->eventStop || $event == $this->eventStart) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $station_id,
                'generator_id' => $generator_id,
                'events'       => [$this->eventStop, $this->eventStart],
            ];
        }
        if ($event == $this->eventMaintenanceStart || $event == $this->eventMaintenanceStop) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $station_id,
                'generator_id' => $generator_id,
                'events'       => [$this->eventMaintenanceStart, $this->eventMaintenanceStop],
            ];
        }
        $db = $model->getLastByStationGIdEvents([], $query);

        // 检查
        if ($db['id'] != $id) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '不能删除该条记录';
            return $this->respond($res);
        }

        if ($db['event'] != $event) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid event';
            return $this->respond($res);
        }

        $result = $model->delById($id);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '删除成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function updateRecord()
    {
        // 检查请求数据
        if (!$this->validate('GeneratorEventUpdateRecord')) {
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
        $event        = $client['event'];
        $newEvent     = [
            'id'           => $client['id'],
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'cause'        => $client['cause'],
            'event_at'     => $client['event_at'],
            'creator'      => $client['creator'],
            'description'  => $client['description'],
        ];

        $model = new RecordModel();

        // 检查
        $db = $model->getById([], $id);
        if (empty($db)) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid id';
            return $this->respond($res);
        }
        if ($db['event'] != $event) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid event';
            return $this->respond($res);
        }

        // 预处理: start stop事件
        if ($event == $this->eventStop || $event == $this->eventStart) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $station_id,
                'generator_id' => $generator_id,
                'events'       => [$this->eventStop, $this->eventStart],
                'event_at'     => $db['event_at'],
            ];
        }
        if ($event == $this->eventMaintenanceStart || $event == $this->eventMaintenanceStop) {
            // 查找时间最近一条
            $query = [
                'station_id'   => $station_id,
                'generator_id' => $generator_id,
                'events'       => [$this->eventMaintenanceStart, $this->eventMaintenanceStop],
                'event_at'     => $db['event_at'],
            ];
        }
        $prev = $model->getPrevByStationGIdEventsTimeStamp([], $query);
        $next = $model->getNextByStationGIdEventsTimeStamp([], $query);

        // if (empty($prev) && empty($next)) {

        // }
        $now = time();
        if (!empty($prev) && empty($next)) {
            if (strtotime($newEvent['event_at']) <= strtotime($prev['event_at'])) {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '修改的日期/时间，早于上一条相同事件类型的记录';
                return $this->respond($res);
            }
            if (strtotime($newEvent['event_at']) >= $now) {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '检查修改的日期/时间';
                return $this->respond($res);
            }
        }
        if (!empty($prev) && !empty($next)) {
            if (strtotime($newEvent['event_at']) <= strtotime($prev['event_at'])) {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '修改的日期/时间，早于上一条相同事件类型的记录';
                return $this->respond($res);
            }
            if (strtotime($newEvent['event_at']) >= strtotime($next['event_at'])) {
                $res['code'] = EXIT_ERROR;
                $res['msg']  = '修改的日期/时间，晚于下一条相同事件类型的记录';
                return $this->respond($res);
            }
        }

        // 修改
        $result = $model->saveRecord($newEvent);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 导出excel文件
    public function getExportRecordsAsExcel()
    {
        $param = $this->request->getGet();

        // 检查输入
        if (!$this->validate('GeneratorEventExportRecords')) {
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

        $model = new RecordModel();

        for ($GID = 1; $GID <= $this->genTotalNumber; $GID++) {
            $query['generator_id'] = $GID;

            $db  = $model->getByStationDateGen($columnName, $query);
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
    }

    public function getStatisticChartData()
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

        $year  = $param['year'];
        $model = new RecordModel();

        // 初值
        $run_time  = [];
        $start_num = [];
        $months    = 12;
        for ($i = 0; $i < $months; $i++) {
            $run_time[$i] = [
                'month' => ($i + 1) . '月',
                'G1'    => 0,
                'G2'    => 0,
                'G3'    => 0,
            ];
            $start_num[$i] = [
                'month' => ($i + 1) . '月',
                'G1'    => 0,
                'G2'    => 0,
                'G3'    => 0,
            ];
        }
        $last_at = [
            'G1' => 'NULL',
            'G2' => 'NULL',
            'G3' => 'NULL',
        ];
        $total_run = [
            'G1_run' => 0,
            'G2_run' => 0,
            'G3_run' => 0,
            'total'  => 0,
        ];
        $total_start = [
            'G1_start' => 0,
            'G2_start' => 0,
            'G3_start' => 0,
        ];

        // 查询记录
        $columnName = ['event', 'event_at'];
        $query      = [
            'station_id'   => $param['station_id'],
            'generator_id' => 0,
            'year'         => $param['year'],
            'event'        => [$this->eventStart, $this->eventStop],
        ];
        for ($i = 1; $i <= $this->genTotalNumber; $i++) {
            $query['generator_id'] = $i;

            $db = $model->getByStationGenYearStartStop($columnName, $query); // 按日期排序
            if (!empty($db)) {
                $cnt = count($db);
                for ($j = 0; $j < $months; $j++) {
                    $start_num2 = 0;
                    $run_time2  = 0;
                    $filter     = [];
                    for ($k = 0; $k < $cnt; $k++) {
                        $item  = $db[$k];
                        $month = date("n", strtotime($item['event_at']));
                        if ($month == ($j + 1)) {
                            $filter[] = $item;
                            // 启动次数
                            if ($item['event'] == $this->eventStart) {
                                $start_num2 = $start_num2 + 1;
                            }
                        }
                    }
                    // 处理：跨月运行
                    if (!empty($filter)) {
                        $temp = reset($filter);
                        if ($temp['event'] == $this->eventStop) {
                            $head = [
                                'event'    => $this->eventStart,
                                'event_at' => my_first_day_of_month($temp['event_at'], 0) . ' 00:00:00',
                            ];
                            array_unshift($filter, $head);
                        }
                        $temp = end($filter);
                        if ($temp['event'] == $this->eventStart) {
                            $now = date("Y-m-d H:i:s");
                            $end = my_last_day_of_month($temp['event_at'], 0) . ' 23:59:59';
                            if (strtotime($now) >= strtotime($end)) {
                                $tail = [
                                    'event'    => $this->eventStop,
                                    'event_at' => $end,
                                ];
                                array_push($filter, $tail);
                            } else {
                                if (strtotime($now) >= strtotime($temp['event_at'])) {
                                    $tail = [
                                        'event'    => $this->eventStop,
                                        'event_at' => $now,
                                    ];
                                    array_push($filter, $tail);
                                }
                            }
                        }
                        // 计算
                        $l = count($filter);
                        for ($n = 0; $n < ($l - 1); $n++) {
                            $start_at = $filter[$n]['event_at'];
                            $stop_at  = $filter[$n + 1]['event_at'];
                            $run_time2 += (strtotime($stop_at) - strtotime($start_at));
                            $n = $n + 1;
                        }
                    }
                    //
                    if ($i == 1) {
                        $start_num[$j]['G1'] = $start_num2;
                        $run_time[$j]['G1']  = round($run_time2 / 3600, 2);
                    }
                    if ($i == 2) {
                        $start_num[$j]['G2'] = $start_num2;
                        $run_time[$j]['G2']  = round($run_time2 / 3600, 2);
                    }
                    if ($i == 3) {
                        $start_num[$j]['G3'] = $start_num2;
                        $run_time[$j]['G3']  = round($run_time2 / 3600, 2);
                    }
                    // 释放
                    unset($filter);
                }
            }
        }

        // 计算：年累计
        $g1 = 0;
        $g2 = 0;
        $g3 = 0;
        for ($i = 0; $i < $month; $i++) {
            $g1 += $run_time[$i]['G1'];
            $g2 += $run_time[$i]['G2'];
            $g3 += $run_time[$i]['G3'];
            //
            $total_start['G1_start'] += $start_num[$i]['G1'];
            $total_start['G2_start'] += $start_num[$i]['G2'];
            $total_start['G3_start'] += $start_num[$i]['G3'];
        }
        $total_run['G1_run'] = round($g1, 2);
        $total_run['G2_run'] = round($g2, 2);
        $total_run['G3_run'] = round($g3, 2);

        $totalHours = 8760;
        if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
            $totalHours = 8784;
        }
        $total_run['total'] = $totalHours;

        $response['code'] = EXIT_SUCCESS;
        $response['data'] = ['start_num' => $start_num, 'run_time' => $run_time, 'last_at' => $last_at, 'total_run' => $total_run, 'total_start' => $total_start];

        return $this->respond($response);
    }

    public function getSyncRecordToKKX()
    {
        $param = $this->request->getGet();

        // 检查输入
        if (!$this->validate('GeneratorEventSyncToKKX')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }
        $date = $param['date'];

        $py_path   = 'python3';
        $py_script = '/var/www/html/mix/server/vendor/python_scripts/kkx/kkx.py';
        exec("$py_path $py_script $date", $msg, $ret);

        if ($ret === 0) {
            $msg2 = str_replace('_n_', "\n", $msg[0]);
            if (strpos($msg2, 'Result') !== false) {
                $res['code'] = EXIT_SUCCESS;
                $msg2        = str_replace('Result', "", $msg2);
            } else {
                $res['code'] = EXIT_ERROR;
            }
            $res['msg'] = $msg2;
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '同步处理出错，稍后再试';
        }
        return $this->respond($res);
    }

    // 内部方法
    protected function validateNewEvent(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($newEvent)) {
            return false;
        }

        $now = time();
        if (!empty($lastEvent)) {
            if ($lastEvent['event'] == $newEvent['event']) {
                return '与保存的上一条事件类型相同';
            }

            // 检查时间戳
            $lastEventAt = strtotime($lastEvent['event_at']);
            $newEventAt  = strtotime($newEvent['event_at']);
            if ($newEventAt <= $lastEventAt || $newEventAt > $now) {
                return '检查日期、时间';
            }
        } else {
            // 检查时间戳
            $newEventAt = strtotime($newEvent['event_at']);
            if ($newEventAt > $now) {
                return '检查日期、时间';
            }
        }

        return true;
    }
}
