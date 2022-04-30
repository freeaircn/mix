<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-01 00:24:22
 */

namespace App\Controllers;

use App\Models\Dts\DeptModel;
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
            case 'statistic':
                $result = $this->_reqStatisticChartData();
                break;
            case 'list':
                $result = $this->_reqList();
                break;
            case 'export_excel':
                $result = $this->_reqExportExcel();
                break;
            case 'sync_kkx':
                $result = $this->_reqSyncToKKX();
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

    public function newRecord()
    {
        if (!$this->validate('GeneratorEventNewRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

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

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

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
        $msg       = $this->_validateNewEvent($lastEvent, $newEvent);
        if ($msg !== true) {
            $res['error'] = $msg;
            return $this->fail($res);
        }

        // 添加
        $db = $model->insertRecord($newEvent);

        if ($db) {
            return $this->respond(['msg' => '已添加一条记录']);
        } else {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }
    }

    public function delRecord()
    {
        if (!$this->validate('GeneratorEventDelRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

        $id           = $client['id'];
        $station_id   = $client['station_id'];
        $generator_id = $client['generator_id'];
        $event        = $client['event'];

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

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
        if (empty($db)) {
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        if ($db['id'] != $id) {
            $res['error'] = '不能删除该条记录';
            return $this->fail($res);
        }

        if ($db['event'] != $event) {
            $res['info']  = 'invalid event';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $result = $model->delById($id);

        if ($result === true) {
            $res['msg'] = '已删除一条记录';
            return $this->respond($res);
        } else {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }
    }

    public function updateRecord()
    {
        if (!$this->validate('GeneratorEventUpdateRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client = $this->request->getJSON(true);

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

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $model = new RecordModel();
        $db    = $model->getById([], $id);
        if (empty($db)) {
            $res['info']  = 'invalid id';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        if ($db['event'] != $event) {
            $res['info']  = 'invalid event';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
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
                $res['error'] = '修改的日期/时间，早于上一条相同事件类型的记录';
                return $this->fail($res);
            }
            if (strtotime($newEvent['event_at']) >= $now) {
                $res['error'] = '检查修改的日期/时间';
                return $this->fail($res);
            }
        }
        if (!empty($prev) && !empty($next)) {
            if (strtotime($newEvent['event_at']) <= strtotime($prev['event_at'])) {
                $res['error'] = '修改的日期/时间，早于上一条相同事件类型的记录';
                return $this->fail($res);
            }
            if (strtotime($newEvent['event_at']) >= strtotime($next['event_at'])) {
                $res['error'] = '修改的日期/时间，晚于下一条相同事件类型的记录';
                return $this->fail($res);
            }
        }

        $result = $model->saveRecord($newEvent);
        if ($result) {
            $res['msg'] = '已修改一条记录';
            return $this->respond($res);
        } else {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }
    }

    // Part-2
    protected function _reqSearchParams()
    {
        $allowReadDeptId = session('allowReadDeptId');
        if (empty($allowReadDeptId)) {
            $res['http_status'] = 200;
            $res['data']        = ['station' => []];
            return $res;
        }

        $model      = new DeptModel();
        $columnName = ['id', 'name'];
        $station    = $model->getByIds($columnName, $allowReadDeptId);

        $res['http_status'] = 200;
        $res['data']        = ['station' => $station];
        return $res;
    }

    protected function _reqStatisticChartData()
    {
        if (!$this->validate('GeneratorEventReqStatistic')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $year       = $params['date'];

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

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

        $model      = new RecordModel();
        $columnName = ['event', 'event_at'];
        $query      = [
            'station_id'   => $station_id,
            'generator_id' => 0,
            'year'         => $year,
            'event'        => [$this->eventStart, $this->eventStop],
        ];
        $month = 0;
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

        // 累计
        if ($month !== 0) {
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
        }

        $res['http_status'] = 200;
        $res['data']        = ['start_num' => $start_num, 'run_time' => $run_time, 'last_at' => $last_at, 'total_run' => $total_run, 'total_start' => $total_start];
        return $res;
    }

    protected function _reqList()
    {
        if (!$this->validate('GeneratorEventReqList')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $query = [
            'station_id'   => $params['station_id'],
            'generator_id' => $params['generator_id'],
            'event'        => $params['event'],
            'description'  => $params['description'],
            'limit'        => $params['limit'],
            'offset'       => $params['offset'],
        ];
        $date = date('Y-m-d', time());

        $model = new RecordModel();

        // 预处理-查询日期
        if (empty($params['date'])) {
            $columnName = ['event_at'];
            $db         = $model->getLastDateByStation($columnName, $station_id);
            if (!isset($db['event_at'])) {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'date' => $date, 'data' => []];
                return $res;
            }
            $date = $db['event_at'];
        } else {
            $regexDate = '/^20\d{2}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])$/';

            if (preg_match($regexDate, $params['date']) === 0) {
                $res['http_status'] = 400;
                $res['msg']         = [
                    'error' => '请求数据无效',
                    'info'  => 'invalid date',
                ];
                return $res;
            }
            $date = $params['date'];
        }

        $query['start'] = my_first_day_of_month($date);
        $query['end']   = my_last_day_of_month($date);

        $columnName = ['id', 'station_id', 'generator_id', 'event', 'cause', 'event_at', 'creator', 'description'];
        $db         = $model->getByStationGIdDateRange($columnName, $query);

        $res['http_status'] = 200;
        $res['data']        = ['total' => $db['total'], 'date' => date('Y-m-d', strtotime($date)), 'data' => $db['result']];
        return $res;
    }

    protected function _reqExportExcel()
    {
        if (!$this->validate('GeneratorEventExportExcel')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $date       = $params['date'];

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $spreadsheet = new Spreadsheet();
        $sheetTitle  = ['序号', '机组', '事件', '时间', '记录人', '说明'];

        $columnName = ['event', 'event_at', 'creator', 'description'];
        $query      = [
            'station_id'   => $station_id,
            'year'         => substr($date, 0, 4),
            'generator_id' => 0,
        ];
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
        // $sheetCount = $spreadsheet->getSheetCount();
        // if ($sheetCount > 1) {
        //     // $spreadsheet->removeSheetByIndex($sheetCount - 1);
        // }

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

    protected function _reqSyncToKKX()
    {

        if (!$this->validate('GeneratorEventSyncToKKX')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $date       = $params['date'];

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        $py_path   = 'python3';
        $py_script = '/var/www/html/mix/server/vendor/python_scripts/kkx/kkx.py';
        exec("$py_path $py_script $date", $msg, $ret);

        if ($ret === 0) {
            $msg2 = str_replace('_n_', "\n", $msg[0]);
            if (strpos($msg2, 'Result') !== false) {
                $res['http_status'] = 200;
                $res['data']        = [];
                $res['msg']         = str_replace('Result', "", $msg2);
                return $res;
            } else {
                $res['http_status'] = 500;
                $res['msg']         = $msg2;
                return $res;
            }
        } else {
            $res['http_status'] = 500;
            $res['msg']         = '服务器处理发生错误，稍后再试';
            return $res;
        }
    }

    protected function _validateNewEvent(array $lastEvent = [], array $newEvent = [])
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
