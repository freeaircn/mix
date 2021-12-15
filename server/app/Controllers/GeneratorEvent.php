<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-12-15 19:04:21
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
        $this->eventStop  = '1';
        $this->eventStart = '2';
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
        // 查找时间最近一条事件
        $lastEvent = $model->getLastRecordByStationGId($station_id, $generator_id);

        // 检查时间，事件的合理性
        $msg = $this->validateNewEvent($lastEvent, $newEvent);
        if ($msg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $msg;
            return $this->respond($res);
        }

        // 添加 DB
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

        $utils          = service('mixUtils');
        $query['start'] = $utils->getFirstDayOfMonth($date);
        $query['end']   = $utils->getLastDayOfMonth($date);

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

        $model = new RecordModel();
        // 查找时间最近
        $db = $model->getLastRecordByStationGId($client['station_id'], $client['generator_id']);

        // 对比
        if ($db['id'] != $client['id']) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '只能删除时间最近的一条记录';
            return $this->respond($res);
        }

        if ($db['event'] != $client['event']) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid event';
            return $this->respond($res);
        }

        $result = $model->delById($client['id']);

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

        // 查找时间最近
        $db = $model->getLastRecordByStationGId($station_id, $generator_id, 2);

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

        // $res['code'] = EXIT_SUCCESS;
        // $res['data'] = ['data' => $param];

        // return $this->respond($res);
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

        $model = new RecordModel();

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

            $db = $model->getByStationDateGen($columnName, $query);
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
}
