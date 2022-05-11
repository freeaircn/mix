<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-05-11 14:59:58
 */

namespace App\Controllers;

use App\Models\Common\DeptModel;
use App\Models\Meter\MeterModel;
use App\Models\Meter\PlanKWhModel;
use CodeIgniter\API\ResponseTrait;

class Meter extends BaseController
{
    use ResponseTrait;

    // 电表的个数
    protected $meterTotalNumber;

    // 主表的编号，全厂电量统计取线路主表记录
    protected $mainMeterId;
    protected $subMeterId;

    // 发电机电表编号
    protected $firstGenMeterId;
    protected $lastGenMeterId;

    // 电表读数倍率
    protected $mainMeterRatio;
    protected $genMeterRatio;
    protected $transformMeterRatio;

    // 单位倍率
    protected $unitRatio;

    // 计算：小数点位数
    protected $precision;

    // 最近的日期
    protected $cacheLatestDate;
    // 第一条记录日期
    protected $first_at;

    public function __construct()
    {
        $this->meterTotalNumber = 9;
        $this->mainMeterId      = 1;
        $this->subMeterId       = 2;
        $this->firstGenMeterId  = 3;
        $this->lastGenMeterId   = 5;

        // 1320000 kWh，4位小数，入库前乘以10000，得到整数
        $this->mainMeterRatio = 132;
        // 500000 kWh，2位小数，入库前乘以100，得到整数
        $this->genMeterRatio = 5000;
        // 20000 kWh，2位小数，入库前乘以100，得到整数
        $this->transformMeterRatio = 200;

        // 数据库 电表读数单位：kwh，页面显示数据单位：万kwh
        $this->unitRatio = 10000;

        $this->precision = 4;

        $this->first_at = '2012-12-31';

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
            case 'statistic_charts':
                $result = $this->_reqStatisticCharts();
                break;
            case 'statistic_overall':
                $result = $this->_reqStatisticOverall();
                break;
            case 'list':
                $result = $this->_reqList();
                break;
            case 'details':
                $result = $this->_reqRecordDetails();
                break;
            case 'daily_report':
                $result = $this->_reqDailyReport();
                break;
            case 'plan_and_deal':
                $result = $this->_reqPlanAndDeal();
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
        // 检查请求数据
        if (!$this->validate('MeterNewRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);

        // 检查数据
        $valid = true;
        if (isset($client['meter'])) {
            if (count($client['meter']) !== $this->meterTotalNumber) {
                $valid = false;
            } else {
                for ($i = 0; $i < $this->meterTotalNumber; $i++) {
                    foreach ($client['meter'][$i] as $value) {
                        if (!is_numeric($value)) {
                            $valid = false;
                            break;
                        }
                    }
                }

            }
        } else {
            $valid = false;
        }
        if (!$valid) {
            $res['info']  = 'invalid meter value';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $station_id       = $client['station_id'];
        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        $meters = $client['meter'];
        $others = [
            'station_id' => $client['station_id'],
            'log_date'   => $client['log_date'],
            'log_time'   => $client['log_time'],
            'creator'    => $client['creator'],
        ];
        $log_date = $client['log_date'];
        $log_time = $client['log_time'];

        $model = new MeterModel();

        // 是否已存在该日期/时间的记录
        $hasLogs = $model->hasSameRecordByStationAndDateTime($station_id, $log_date, $log_time);
        if ($hasLogs) {
            $res['error'] = '输入的日期已存在记录';
            return $this->fail($res);
        }

        $result = $model->createMeterMultiRecords($meters, $others);
        if ($result === false) {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }

        $res['msg'] = '已保存';
        return $this->respond($res);
    }

    public function updateRecord()
    {
        if (!$this->validate('MeterNewRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client     = $this->request->getJSON(true);
        $station_id = $client['station_id'];

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        // 检查数据
        $valid = true;
        if (isset($client['meter'])) {
            if (count($client['meter']) !== $this->meterTotalNumber) {
                $valid = false;
            } else {
                for ($i = 0; $i < $this->meterTotalNumber; $i++) {
                    foreach ($client['meter'][$i] as $value) {
                        if (!is_numeric($value)) {
                            $valid = false;
                            break;
                        }
                    }
                }

            }
        } else {
            $valid = false;
        }
        if (!$valid) {
            $res['info']  = 'invalid meter value';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        // 取出数据
        $meters = $client['meter'];
        $others = [
            'creator' => $client['creator'],
        ];
        $where = [
            'station_id' => $client['station_id'],
            'log_date'   => $client['log_date'],
            'log_time'   => $client['log_time'],
        ];

        $model  = new MeterModel();
        $result = $model->updateMeterMultiRecords($meters, $others, $where);

        if ($result === false) {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }

        $res['msg'] = '已修改';
        return $this->respond($res);
    }

    public function delRecord()
    {
        // 检查请求数据
        if (!$this->validate('MeterDelRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $client = $this->request->getJSON(true);

        $id         = $client['id'];
        $station_id = $client['station_id'];
        $log_date   = $client['log_date'];
        $log_time   = $client['log_time'];

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        // 根据id查找记录
        $model = new MeterModel();
        $db    = $model->getMeterRecordById($id);
        if (empty($db)) {
            $res['info']  = 'invalid id';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        // 对比
        if ($db['station_id'] !== $station_id || $db['log_date'] !== $log_date || $db['log_time'] !== $log_time || $db['meter_id'] !== '1') {
            $res['info']  = 'invalid record';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        // 执行删除，多电表
        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];
        $result = $model->delMeterRecordsByStationDateTime($query);

        if ($result === false) {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }

        $res['msg'] = '已删除';
        return $this->respond($res);
    }

    public function updatePlanAndDealRecord()
    {
        if (!$this->validate('MeterUpdatePlanAndDealRecord')) {
            $res['info']  = $this->validator->getErrors();
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }
        $client     = $this->request->getJSON(true);
        $station_id = $client['station_id'];

        $allowWriteDeptId = session('allowWriteDeptId');
        if (!in_array($station_id, $allowWriteDeptId)) {
            return $this->failUnauthorized('用户没有权限');
        }

        if (!is_numeric($client['planning']) || !is_numeric($client['deal'])) {
            $res['info']  = 'invalid planning or deal';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        $id    = $client['id'];
        $query = [
            'station_id' => $client['station_id'],
            'year'       => $client['year'],
            'month'      => $client['month'],
        ];

        $data = [
            'id'       => $client['id'],
            'planning' => $client['planning'],
            'deal'     => $client['deal'],
            'creator'  => $this->session->get('username'),
        ];

        // 与库中的数据比较
        $model = new PlanKWhModel();
        $db    = $model->getKwhPlanRecordsByStationYearMonth($client['station_id'], $client['year'], $client['month']);
        if ($id !== $db[0]['id']) {
            $res['info']  = 'conflict with DB';
            $res['error'] = '请求数据无效';
            return $this->fail($res);
        }

        // 修改
        $result = $model->updateKwhPlanRecord($data);
        if ($result === false) {
            $res['error'] = '服务器处理发生错误，稍候再试';
            return $this->fail($res);
        }

        $res['msg'] = '已修改';
        return $this->respond($res);
    }

    // Part-2
    protected function _reqList()
    {
        if (!$this->validate('MeterReqList')) {
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
            'station_id' => $params['station_id'],
            'limit'      => $params['limit'],
            'offset'     => $params['offset'],
        ];
        $date = date('Y-m-d', time());

        $model = new MeterModel();

        // 预处理-查询日期
        if (empty($params['date'])) {
            $query2 = ['station_id' => $query['station_id']];
            $fields = ['log_date'];
            $db     = $model->getMeterLatestRecordsByStation($fields, $query2);
            if (!isset($db['log_date'])) {
                $res['http_status'] = 200;
                $res['data']        = ['total' => 0, 'date' => $date, 'data' => []];
                return $res;
            }
            $date = $db['log_date'];
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

        // 限定查询日期
        $query['start'] = my_first_day_of_month($date);
        $query['end']   = my_last_day_of_month($date);

        $fields = ['id', 'station_id', 'log_date', 'log_time', 'creator'];
        $db     = $model->getMeterRecordsForListView($fields, $query);

        $res['http_status'] = 200;
        $res['data']        = ['date' => date('Y-m-d', strtotime($date)), 'total' => $db['total'], 'data' => $db['data']];
        return $res;
    }

    protected function _reqRecordDetails()
    {
        if (!$this->validate('MeterReqRecordDetail')) {
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

        $model  = new MeterModel();
        $fields = ['meter_id', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley'];
        $db     = $model->getMeterRecordsByStationDateTime($fields, $params['station_id'], $params['log_date'], $params['log_time']);

        // 指定日期+时间的记录不存在
        if (empty($db)) {
            $res['http_status'] = 200;
            $res['data']        = ['size' => 0, 'record' => []];
            return $res;
        }

        $cnt    = count($db);
        $result = [];
        for ($i = 0; $i < $cnt; $i++) {
            // 线路表：4位小数
            if ($db[$i]['meter_id'] < 3) {
                $result[$i]['fak'] = $db[$i]['fak'] / 10000;
                $result[$i]['bak'] = $db[$i]['bak'] / 10000;
                $result[$i]['frk'] = $db[$i]['frk'] / 10000;
                $result[$i]['brk'] = $db[$i]['brk'] / 10000;
            }
            // 非线路表：2位小数
            if ($db[$i]['meter_id'] > 2) {
                $result[$i]['fak']    = $db[$i]['fak'] / 100;
                $result[$i]['bak']    = $db[$i]['bak'] / 100;
                $result[$i]['frk']    = $db[$i]['frk'] / 100;
                $result[$i]['brk']    = $db[$i]['brk'] / 100;
                $result[$i]['peak']   = $db[$i]['peak'] / 100;
                $result[$i]['valley'] = $db[$i]['valley'] / 100;
            }
        }

        $res['http_status'] = 200;
        $res['data']        = ['size' => $cnt, 'record' => $result];
        return $res;
    }

    protected function _reqDailyReport()
    {
        if (!$this->validate('MeterReqDailyReport')) {
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

        $log_date = $params['log_date'];
        $log_time = $params['log_time'];

        // 查询年计划、月成交量
        // $year        = date('Y', strtotime($log_date));
        $planAndDeal = $this->_getPlanAndDealForStatisticChart($station_id, date('Y', strtotime($log_date)));

        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];

        if (strpos($log_time, '23:59') !== false) {
            $reportData = $this->_getDailyReportData($query);

            $rate['plan']['month']   = '0%';
            $rate['plan']['quarter'] = '0%';
            $rate['plan']['year']    = '0%';
            $rate['deal']['month']   = '0%';

            $month_index   = date('n', strtotime($log_date));
            $quarter_index = ceil($month_index / 3);

            // 月
            if ($planAndDeal['month'][$month_index - 1]['plan'] > 0) {
                $temp                  = round($reportData['real']['month'] / $planAndDeal['month'][$month_index - 1]['plan'], $this->precision);
                $rate['plan']['month'] = ($temp * 100) . '%';
            }

            // 季度
            if ($planAndDeal['quarter'][$quarter_index - 1]['plan'] > 0) {
                $temp                    = round($reportData['real']['quarter'] / $planAndDeal['quarter'][$quarter_index - 1]['plan'], $this->precision);
                $rate['plan']['quarter'] = ($temp * 100) . '%';
            }

            // 年度
            if ($planAndDeal['totalPlan'] > 0) {
                $temp                 = round($reportData['real']['year'] / $planAndDeal['totalPlan'], $this->precision);
                $rate['plan']['year'] = ($temp * 100) . '%';
            }

            // 月成交完成率：上网 / 成交
            if ($planAndDeal['month'][$month_index - 1]['deal'] > 0) {
                $temp                  = round($reportData['up']['month'] / $planAndDeal['month'][$month_index - 1]['deal'], $this->precision);
                $rate['deal']['month'] = ($temp * 100) . '%';
            }

            //
            $model       = new DeptModel();
            $db          = $model->getDeptRecordById([], $station_id);
            $stationName = isset($db['name']) ? $db['name'] : '电厂';

            // 简报
            $report = $this->_reportDailyText($stationName, $log_date, $rate, $reportData);

            $res['http_status'] = 200;
            $res['data']        = ['data' => $report, 'type' => '23'];
            return $res;
        }

        // 针对 20:00
        if (strpos($log_time, '20:00') !== false) {
            $genEnergy = $this->_getDailyReportData20Clock($query);

            $res['http_status'] = 200;
            $res['data']        = ['data' => $genEnergy, 'type' => '20'];
            return $res;
        }

        $res['http_status'] = 500;
        $res['msg']         = '服务器处理发生错误，稍候再试';
        return $res;
    }

    protected function _reqPlanAndDeal()
    {
        if (!$this->validate('MeterReqPlanAndDeal')) {
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

        $year  = substr($date, 0, 4);
        $model = new PlanKWhModel();
        $db    = $model->getKwhPlanRecordsByStationYear($station_id, $year);

        $res['http_status'] = 200;
        $res['data']        = ['data' => $db];
        return $res;
    }

    protected function _reqStatisticCharts()
    {
        if (!$this->validate('MeterReqStatisticCharts')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $date       = $params['date'] . '-12-31';
        $log_time   = '23:59:00';

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        // 查找最近一条23:59记录的日期
        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
        ];
        $query['start_at'] = my_first_day_of_year($date);
        $query['end_at']   = my_last_day_of_year($date);

        $model  = new MeterModel();
        $fields = ['log_date'];
        $db     = $model->getMeterRecordsByStationDateRangeTime($fields, $query);
        if (!isset($db['log_date'])) {
            $res['http_status'] = 200;
            return $res;
        }

        $log_date = $db['log_date'];

        // 查询计划、成交
        // $year        = date('Y', strtotime($log_date));
        $planAndDeal = $this->_getPlanAndDealForStatisticChart($station_id, date('Y', strtotime($log_date)));

        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];

        // 统计图
        $days = $this->_get30Days($query);

        $months    = $this->_get12Months($query);
        $cnt       = count($months);
        $totalReal = 0;
        $totalUp   = 0;
        for ($i = 0; $i < $cnt; $i++) {
            $months[$i]['plan'] = $planAndDeal['month'][$i]['plan'];
            $months[$i]['deal'] = $planAndDeal['month'][$i]['deal'];
            $totalReal          = $totalReal + $months[$i]['real'];
            $totalUp            = $totalUp + $months[$i]['up'];
        }

        $quarters = $this->_get4Quarters($query);
        $cnt      = count($quarters);
        for ($i = 0; $i < $cnt; $i++) {
            $quarters[$i]['plan'] = $planAndDeal['quarter'][$i]['plan'];
            $quarters[$i]['deal'] = $planAndDeal['quarter'][$i]['deal'];
        }

        // 计算：完成率
        $index = date('n', strtotime($log_date)) - 1;

        $temp = 0;
        if ($planAndDeal['totalPlan'] > 0) {
            $temp = round(($totalReal * $this->unitRatio) / ($planAndDeal['totalPlan'] * $this->unitRatio), $this->precision);
        } else {
            $temp = 0;
        }
        $rate['yearPlan'] = strval($temp * 100);

        if ($planAndDeal['month'][$index]['plan'] > 0) {
            $temp = round(($months[$index]['real'] * $this->unitRatio) / ($planAndDeal['month'][$index]['plan'] * $this->unitRatio), $this->precision);
        } else {
            $temp = 0;
        }
        $rate['monthPlan'] = strval($temp * 100);

        if ($planAndDeal['totalDeal'] > 0) {
            $temp = round(($totalUp * $this->unitRatio) / ($planAndDeal['totalDeal'] * $this->unitRatio), $this->precision);
        } else {
            $temp = 0;
        }
        $rate['yearDeal'] = strval($temp * 100);

        if ($planAndDeal['month'][$index]['deal'] > 0) {
            $temp = round(($months[$index]['up'] * $this->unitRatio) / ($planAndDeal['month'][$index]['deal'] * $this->unitRatio), $this->precision);
        } else {
            $temp = 0;
        }
        $rate['monthDeal'] = strval($temp * 100);

        $year['plan'] = $planAndDeal['totalPlan'];
        $year['deal'] = $planAndDeal['totalDeal'];

        // 结果
        $res['http_status'] = 200;
        $res['data']        = [
            'date'     => $log_date,
            //
            'year'     => $year,
            'rate'     => $rate,
            //
            'days'     => $days,
            'months'   => $months,
            'quarters' => $quarters,
        ];
        return $res;
    }

    protected function _reqStatisticOverall()
    {
        if (!$this->validate('MeterReqStatisticOverall')) {
            $res['http_status'] = 400;
            $res['msg']         = [
                'error' => '请求数据无效',
                'info'  => $this->validator->getErrors(),
            ];
            return $res;
        }

        $params     = $this->request->getGet();
        $station_id = $params['station_id'];
        $log_time   = '23:59:00';

        $allowReadDeptId = session('allowReadDeptId');
        if (!in_array($station_id, $allowReadDeptId)) {
            $res['http_status'] = 401;
            $res['msg']         = '用户没有权限';
            return $res;
        }

        // 查找最近一条23:59记录的日期
        $meter_ids   = [];
        $meter_ids[] = $this->mainMeterId;
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $model = new MeterModel();
        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $fields = ['log_date', 'meter_id', 'fak'];
        $db3    = $model->getMeterLatestRecordsByStationTimeMeters($fields, $query, count($meter_ids));

        $log_date = $db3[0]['log_date'];

        // 自投运
        $total = [
            'onGridEnergy' => 0,
            'genEnergy'    => 0,
        ];
        $cnt  = count($db3);
        $temp = 0;
        for ($i = 0; $i < $cnt; $i++) {
            if ($db3[$i]['meter_id'] == $this->mainMeterId) {
                $total['onGridEnergy'] = $db3[$i]['fak'];
            } else {
                $temp = $temp + $db3[$i]['fak'];
            }
        }
        $total['genEnergy'] = $temp;

        // 查找全景数据
        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
            'meter_id'   => $this->mainMeterId,
        ];

        $db        = $this->_getForOverallStatistic($query);
        $yearData  = $db['yearData'];
        $monthData = $db['monthData'];

        $result = $this->_chgUnitForOverallStatistic($total, $yearData, $monthData);

        $res['http_status'] = 200;
        $res['data']        = $result;
        return $res;
    }

    // Part - 3
    protected function _getForOverallStatistic($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];
        $meter_id   = $query['meter_id'];

        // 每月最后一天的日期
        $first_at = $this->first_at;
        $month    = [];
        $month[]  = $first_at;

        $target   = my_last_day_of_month($log_date);
        $i        = 1;
        $continue = true;
        do {
            $temp = my_last_day_of_month($first_at, $i);
            if ($temp === $target) {
                $continue = false;
                $month[]  = $log_date;
            } else {
                $month[] = $temp;
            }
            $i++;
        } while ($continue);

        // 月 初值0
        $monthData = [];
        for ($j = 0; $j < ($i - 1); $j++) {
            $monthData[$j] = [
                'date'  => substr($month[$j + 1], 0, 7),
                'value' => 0,
            ];
        }

        // 每年最后一天的日期
        $first_at = $this->first_at;
        $year     = [];
        $year[]   = $first_at;

        $target   = my_last_day_of_year($log_date);
        $i        = 1;
        $continue = true;
        do {
            $temp = my_last_day_of_year($first_at, $i);
            if ($temp === $target) {
                $continue = false;
                $year[]   = $log_date;
            } else {
                $year[] = $temp;
            }
            $i++;
        } while ($continue);

        // 年 初值0
        $yearData = [];
        for ($j = 0; $j < ($i - 1); $j++) {
            $yearData[$j] = [
                'date'  => substr($year[$j + 1], 0, 4),
                'value' => 0,
            ];
        }

        // 查询
        $model = new MeterModel();

        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $month,
            'log_time'   => $log_time,
            'meter_id'   => $meter_id,
        ];
        $fields = ['log_date, fak'];
        $db     = $model->getMeterRecordsByStationDatesTimeMeter($fields, $query2);
        if (empty($db)) {
            return ['yearData' => $yearData, 'monthData' => $monthData];
        }

        // 插入
        $db2  = [];
        $cnt  = count($month);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $temp = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($month[$i] === $db[$j]['log_date']) {
                    $temp = $db[$j]['fak'];
                    break;
                }
            }
            $db2[] = $temp;
        }

        // delta
        $cnt = count($db2);
        for ($i = 0; $i < ($cnt - 1); $i++) {
            if (($db2[$i + 1] > $db2[$i]) && ($db2[$i] > 0)) {
                $monthData[$i]['value'] = $db2[$i + 1] - $db2[$i];
            }
        }

        // 插入
        $db2  = [];
        $cnt  = count($year);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $temp = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($year[$i] === $db[$j]['log_date']) {
                    $temp = $db[$j]['fak'];
                    break;
                }
            }
            $db2[] = $temp;
        }

        // delta
        $cnt = count($db2);
        for ($i = 0; $i < ($cnt - 1); $i++) {
            if (($db2[$i + 1] > $db2[$i]) && ($db2[$i] > 0)) {
                $yearData[$i]['value'] = $db2[$i + 1] - $db2[$i];
            }
        }

        return ['yearData' => $yearData, 'monthData' => $monthData];
    }

    protected function _chgUnitForOverallStatistic($total, $yearData, $monthData)
    {

        // 上网，电表倍率，单位换算 kWh -> 万kWh
        $total['onGridEnergy'] = round($total['onGridEnergy'] * $this->mainMeterRatio / 10000, $this->precision);
        $total['genEnergy']    = round($total['genEnergy'] * $this->genMeterRatio / 10000, $this->precision);

        $cnt = count($yearData);
        for ($i = 0; $i < $cnt; $i++) {
            $yearData[$i]['value'] = round($yearData[$i]['value'] * $this->mainMeterRatio / 10000, $this->precision);
        }

        $cnt = count($monthData);
        for ($i = 0; $i < $cnt; $i++) {
            $monthData[$i]['value'] = round($monthData[$i]['value'] * $this->mainMeterRatio / 10000, $this->precision);
        }

        return ['total' => $total, 'yearData' => $yearData, 'monthData' => $monthData];

    }

    protected function _getPlanAndDealForStatisticChart(string $station_id = null, string $year = null)
    {
        // 初值
        $month = [];
        for ($i = 0; $i < 12; $i++) {
            $month[$i] = [
                'index' => ($i + 1) . '月',
                'plan'  => 0,
                'deal'  => 0,
            ];
        }

        $quarter = [];
        for ($i = 0; $i < 4; $i++) {
            $quarter[$i] = [
                'index' => ($i + 1) . '季度',
                'plan'  => 0,
                'deal'  => 0,
            ];
        }

        $totalPlan = 0;
        $totalDeal = 0;

        //
        $model = new PlanKWhModel();
        $db    = $model->getKwhPlanRecordsByStationYear($station_id, $year);

        if (empty($db)) {
            return [
                'month'   => $month,
                'quarter' => $quarter,
            ];
        }

        // 单位：DB kwh -> Web 万kWh
        $cnt = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $month[$i]['plan'] = $db[$i]['planning'] / $this->unitRatio;
            $month[$i]['deal'] = $db[$i]['deal'] / $this->unitRatio;
            $totalPlan         = $totalPlan + $db[$i]['planning'];
            $totalDeal         = $totalDeal + $db[$i]['deal'];
        }
        $totalPlan = $totalPlan / $this->unitRatio;
        $totalDeal = $totalDeal / $this->unitRatio;

        $quarter[0]['plan'] = ($db[0]['planning'] + $db[1]['planning'] + $db[2]['planning']) / $this->unitRatio;
        $quarter[1]['plan'] = ($db[3]['planning'] + $db[4]['planning'] + $db[5]['planning']) / $this->unitRatio;
        $quarter[2]['plan'] = ($db[6]['planning'] + $db[7]['planning'] + $db[8]['planning']) / $this->unitRatio;
        $quarter[3]['plan'] = ($db[9]['planning'] + $db[10]['planning'] + $db[11]['planning']) / $this->unitRatio;

        $quarter[0]['deal'] = ($db[0]['deal'] + $db[1]['deal'] + $db[2]['deal']) / $this->unitRatio;
        $quarter[1]['deal'] = ($db[3]['deal'] + $db[4]['deal'] + $db[5]['deal']) / $this->unitRatio;
        $quarter[2]['deal'] = ($db[6]['deal'] + $db[7]['deal'] + $db[8]['deal']) / $this->unitRatio;
        $quarter[3]['deal'] = ($db[9]['deal'] + $db[10]['deal'] + $db[11]['deal']) / $this->unitRatio;

        return [
            'month'     => $month,
            'quarter'   => $quarter,
            'totalPlan' => $totalPlan,
            'totalDeal' => $totalDeal,
        ];
    }

    // 2021-11-21
    protected function _get30Days($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $length   = 30;
        $start_at = my_any_day($log_date, -$length);
        $end_at   = $log_date;

        // 初值
        $res      = [];
        $dates[0] = $start_at;
        for ($i = 0; $i < $length; $i++) {
            $date    = my_any_day($log_date, -($length - 1 - $i));
            $res[$i] = [
                'date' => $date,
                'real' => 0,
                'up'   => 0,
            ];
            $dates[$i + 1] = $date;
        }

        // 查询线路主表，发电机表
        $query2 = [
            'station_id'     => $station_id,
            'start_at'       => $start_at,
            'end_at'         => $end_at,
            'log_time'       => $log_time,
            'first_meter_id' => $this->mainMeterId,
            'last_meter_id'  => $this->lastGenMeterId,
        ];
        $fields = ['log_date', 'meter_id', 'fak'];

        $model = new MeterModel();
        $db    = $model->getMeterRecordsByStationDateRangeTimeMeters($fields, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $real = [];
        $up   = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $up[$date] = ['fak' => $item['fak']];
                    }
                    continue;
                }
                if ($item['meter_id'] == $this->subMeterId) {
                    continue;
                }
                if ($item['log_date'] === $date) {
                    $fak += $item['fak'];
                }
            }
            $real[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($real[$b]['fak'] > $real[$a]['fak']) && ($real[$a]['fak'] > 0)) {
                $res[$i]['real'] = ($real[$b]['fak'] - $real[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($up[$b]['fak'] > $up[$a]['fak']) && ($up[$a]['fak'] > 0)) {
                $res[$i]['up'] = ($up[$b]['fak'] - $up[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;
    }

    // 2021-11-21
    protected function _get12Months($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $length = 12;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'month' => ($i + 1) . '月',
                'real'  => 0,
                'up'    => 0,
                'down'  => 0,
            ];
        }

        // 每月的最后一天的日期
        $month = date('n', strtotime($log_date));
        $dates = [];
        for ($i = 0; $i < $month; $i++) {
            $dates[] = my_last_day_of_month($log_date, (-$month + $i));
        }
        $dates[] = $log_date;

        $meter_ids[0] = $this->mainMeterId;
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $fields = ['log_date', 'meter_id', 'fak', 'bak'];

        $model = new MeterModel();
        $db    = $model->getMeterRecordsByStationDatesTimeMeters($fields, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $real = [];
        $up   = [];
        $down = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $up[$date]   = ['fak' => $item['fak']];
                        $down[$date] = ['bak' => $item['bak']];
                    }
                    continue;
                }
                if ($item['meter_id'] == $this->subMeterId) {
                    continue;
                }
                if ($item['log_date'] === $date) {
                    $fak += $item['fak'];
                }
            }
            $real[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($dates) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($real[$b]['fak'] > $real[$a]['fak']) && ($real[$a]['fak'] > 0)) {
                $res[$i]['real'] = ($real[$b]['fak'] - $real[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($up[$b]['fak'] > $up[$a]['fak']) && ($up[$a]['fak'] > 0)) {
                $res[$i]['up'] = ($up[$b]['fak'] - $up[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
            if (($down[$b]['bak'] > $down[$a]['bak']) && ($down[$a]['bak'] > 0)) {
                $res[$i]['down'] = ($down[$b]['bak'] - $down[$a]['bak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;
    }

    // 2021-11-21
    protected function _get4Quarters($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $length = 4;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'quarter' => ($i + 1) . '季度',
                'real'    => 0,
                'up'      => 0,
            ];
        }

        // 每季度的最后一天的日期
        $quarter = ceil((date('n', strtotime($log_date))) / 3);
        $dates   = [];
        for ($i = 0; $i < $quarter; $i++) {
            $dates[] = my_last_day_of_quarter($log_date, (-$quarter + $i));
        }
        $dates[] = $log_date;

        $meter_ids[0] = $this->mainMeterId;
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $fields = ['log_date', 'meter_id', 'fak'];

        $model = new MeterModel();
        $db    = $model->getMeterRecordsByStationDatesTimeMeters($fields, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $real = [];
        $up   = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $up[$date] = ['fak' => $item['fak']];
                    }
                    continue;
                }
                if ($item['meter_id'] == $this->subMeterId) {
                    continue;
                }
                if ($item['log_date'] === $date) {
                    $fak += $item['fak'];
                }
            }
            $real[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($dates) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($real[$b]['fak'] > $real[$a]['fak']) && ($real[$a]['fak'] > 0)) {
                $res[$i]['real'] = ($real[$b]['fak'] - $real[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($up[$b]['fak'] > $up[$a]['fak']) && ($up[$a]['fak'] > 0)) {
                $res[$i]['up'] = ($up[$b]['fak'] - $up[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;
    }

    protected function _getDailyReportData($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 初值0
        $real = [
            'day'        => 0,
            'week'       => 0,
            'month'      => 0,
            'quarter'    => 0,
            'year'       => 0,
            'day_peak'   => 0,
            'day_valley' => 0,
            'day_frk'    => 0,
            'day_brk'    => 0,
        ];
        $up = [
            'day'     => 0,
            'week'    => 0,
            'month'   => 0,
            'quarter' => 0,
            'year'    => 0,
        ];
        $res = [
            'real' => $real,
            'up'   => $up,
        ];

        // 需查找的日期
        $today       = $log_date;
        $prevDay     = my_any_day($today, -1);
        $prevWeek    = my_prev_week($today);
        $prevMonth   = my_last_day_of_month($today, -1);
        $prevQuarter = my_last_day_of_quarter($today, -1);
        $prevYear    = my_last_day_of_year($today, -1);
        $dates       = [$today, $prevDay, $prevWeek, $prevMonth, $prevQuarter, $prevYear];

        $meter_ids[0] = $this->mainMeterId;
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        // 查询给定日期：表读数，峰谷电量
        $model = new MeterModel();

        $fields = ['meter_id', 'log_date', 'fak', 'frk', 'brk', 'peak', 'valley'];
        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $db = $model->getMeterRecordsByStationDatesTimeMeters($fields, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $db2_real = [];
        $db2_up   = [];
        $cnt      = count($dates);
        $cnt2     = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date   = $dates[$i];
            $fak    = 0;
            $frk    = 0;
            $brk    = 0;
            $peak   = 0;
            $valley = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $db2_up[$date] = ['fak' => $item['fak']];
                    }
                    continue;
                }
                if ($item['meter_id'] == $this->subMeterId) {
                    continue;
                }
                if ($item['log_date'] === $date) {
                    $fak += $item['fak'];
                    $frk += $item['frk'];
                    $brk += $item['brk'];
                    $peak += $item['peak'];
                    $valley += $item['valley'];
                }
            }
            $db2_real[$date] = ['fak' => $fak, 'frk' => $frk, 'brk' => $brk, 'peak' => $peak, 'valley' => $valley];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        // 单日 - 发电
        if (($db2_real[$today]['fak'] > $db2_real[$prevDay]['fak']) && ($db2_real[$prevDay]['fak'] > 0)) {
            $real['day'] = ($db2_real[$today]['fak'] - $db2_real[$prevDay]['fak']) * $this->genMeterRatio / $this->unitRatio;
        }
        if (($db2_real[$today]['peak'] > $db2_real[$prevDay]['peak']) && ($db2_real[$prevDay]['peak'] > 0)) {
            $real['day_peak'] = ($db2_real[$today]['peak'] - $db2_real[$prevDay]['peak']) * $this->genMeterRatio / $this->unitRatio;
        }
        if (($db2_real[$today]['valley'] > $db2_real[$prevDay]['valley']) && ($db2_real[$prevDay]['valley'] > 0)) {
            $real['day_valley'] = ($db2_real[$today]['valley'] - $db2_real[$prevDay]['valley']) * $this->genMeterRatio / $this->unitRatio;
        }

        if (($db2_real[$today]['frk'] > $db2_real[$prevDay]['frk']) && ($db2_real[$prevDay]['frk'] > 0)) {
            $real['day_frk'] = ($db2_real[$today]['frk'] - $db2_real[$prevDay]['frk']) * $this->genMeterRatio / $this->unitRatio;
        }
        if (($db2_real[$today]['brk'] > $db2_real[$prevDay]['brk']) && ($db2_real[$prevDay]['brk'] > 0)) {
            $real['day_brk'] = ($db2_real[$today]['brk'] - $db2_real[$prevDay]['brk']) * $this->genMeterRatio / $this->unitRatio;
        }

        // 单日 - 上网
        if (($db2_up[$today]['fak'] > $db2_up[$prevDay]['fak']) && ($db2_up[$prevDay]['fak'] > 0)) {
            $up['day'] = ($db2_up[$today]['fak'] - $db2_up[$prevDay]['fak']) * $this->mainMeterRatio / $this->unitRatio;
        }

        // 七天
        if (($db2_real[$today]['fak'] > $db2_real[$prevWeek]['fak']) && ($db2_real[$prevWeek]['fak'] > 0)) {
            $real['week'] = ($db2_real[$today]['fak'] - $db2_real[$prevWeek]['fak']) * $this->genMeterRatio / $this->unitRatio;
        }

        if (($db2_up[$today]['fak'] > $db2_up[$prevWeek]['fak']) && ($db2_up[$prevWeek]['fak'] > 0)) {
            $up['week'] = ($db2_up[$today]['fak'] - $db2_up[$prevWeek]['fak']) * $this->mainMeterRatio / $this->unitRatio;
        }

        // 月
        if (($db2_real[$today]['fak'] > $db2_real[$prevMonth]['fak']) && ($db2_real[$prevMonth]['fak'] > 0)) {
            $real['month'] = ($db2_real[$today]['fak'] - $db2_real[$prevMonth]['fak']) * $this->genMeterRatio / $this->unitRatio;
        }

        if (($db2_up[$today]['fak'] > $db2_up[$prevMonth]['fak']) && ($db2_up[$prevMonth]['fak'] > 0)) {
            $up['month'] = ($db2_up[$today]['fak'] - $db2_up[$prevMonth]['fak']) * $this->mainMeterRatio / $this->unitRatio;
        }

        // 季度
        if (($db2_real[$today]['fak'] > $db2_real[$prevQuarter]['fak']) && ($db2_real[$prevQuarter]['fak'] > 0)) {
            $real['quarter'] = ($db2_real[$today]['fak'] - $db2_real[$prevQuarter]['fak']) * $this->genMeterRatio / $this->unitRatio;
        }

        if (($db2_up[$today]['fak'] > $db2_up[$prevQuarter]['fak']) && ($db2_up[$prevQuarter]['fak'] > 0)) {
            $up['quarter'] = ($db2_up[$today]['fak'] - $db2_up[$prevQuarter]['fak']) * $this->mainMeterRatio / $this->unitRatio;
        }

        // 年
        if (($db2_real[$today]['fak'] > $db2_real[$prevYear]['fak']) && ($db2_real[$prevYear]['fak'] > 0)) {
            $real['year'] = ($db2_real[$today]['fak'] - $db2_real[$prevYear]['fak']) * $this->genMeterRatio / $this->unitRatio;
        }

        if (($db2_up[$today]['fak'] > $db2_up[$prevYear]['fak']) && ($db2_up[$prevYear]['fak'] > 0)) {
            $up['year'] = ($db2_up[$today]['fak'] - $db2_up[$prevYear]['fak']) * $this->mainMeterRatio / $this->unitRatio;
        }

        $res = [
            'real' => $real,
            'up'   => $up,
        ];

        return $res;
    }

    protected function _getDailyReportData20Clock($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 初值0
        $res = 0;

        // 需查找的日期
        $today   = $log_date;
        $prevDay = my_any_day($log_date, -1);
        $dates   = [$today, $prevDay];

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        // 查询给定日期：表读数，正向有功
        $model = new MeterModel();

        $fields = ['log_date', 'fak'];
        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $db = $model->getMeterRecordsByStationDatesTimeMeters($fields, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($db[$j]['log_date'] === $date) {
                    $fak += $db[$j]['fak'];
                }
            }
            $db2[$date] = [
                'fak' => $fak,
            ];
        }

        // 单日
        if (($db2[$today]['fak'] >= $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $value = $db2[$today]['fak'] - $db2[$prevDay]['fak'];
            // 电表倍率，单位 kWh -> 万kWh
            $res = round($value * $this->genMeterRatio / 10000, $this->precision);
        }

        return $res;
    }

    protected function _reportDailyText($stationName, $date, $rate, $reportData)
    {
        $res = [
            'extra1' => '',
            'extra2' => '',
            'daily1' => '',
            'weekly' => '',
            'daily2' => '',
        ];

        // 日期
        $timestamp = strtotime($date);
        $year      = date('Y', $timestamp) . '年';
        $month     = date('m', $timestamp) . '月';
        $day       = date('d', $timestamp) . '日';

        $arr     = ['一', '二', '三', '四'];
        $quarter = $arr[ceil(date('n', $timestamp) / 3) - 1];

        $res['extra1'] = '今日全机组：正向无功 ' . $reportData['real']['day_frk'] . ' 万kVar，'
            . '反向无功 ' . $reportData['real']['day_brk'] . ' 万kVar。';

        $res['extra2'] = '今日全机组：高峰发电量 ' . $reportData['real']['day_peak'] . ' 万kWh，'
            . '低谷发电量 ' . $reportData['real']['day_valley'] . ' 万kWh。';

        $res['daily1'] = $stationName . ' '
            . $year . $month . $day . '，今日发电量 ' . $reportData['real']['day'] . ' 万kWh。'
            . '截止' . $month . $day . '24时，本月累计发电量 ' . $reportData['real']['month'] . ' 万kWh，完成月度发电计划的 ' . $rate['plan']['month'] . '，第'
            . $quarter . '季度累计发电量 ' . $reportData['real']['quarter'] . ' 万kWh，完成季度发电计划的 ' . $rate['plan']['quarter'] . '，'
            . '本年累计发电量 ' . $reportData['real']['year'] . ' 万kWh，完成年度发电计划的 ' . $rate['plan']['year'] . '。'
            . '今日弃水电量为 0 万kWh，本月弃水电量累计 0 万kWh。';

        $res['daily2'] = $stationName . ' '
            . $year . $month . $day . '，发电机组为#1、#2、#3机组，出力在 MW 到 MW 之间，'
            . '今日发电量 ' . $reportData['real']['day'] . ' 万kWh，'
            . '今日上网电量 ' . $reportData['up']['day'] . ' 万kWh。'
            . '截止' . $month . $day . '24时，本月累计上网电量 ' . $reportData['up']['month'] . ' 万kWh，完成月度成交电量的 ' . $rate['deal']['month'] . '，'
            . '本年累计上网电量 ' . $reportData['up']['year'] . ' 万kWh。'
            . '今日弃水电量为 0 万kWh，本月弃水电量累计 0 万kWh。';

        //
        $temp      = my_any_day($date, 1);
        $timestamp = strtotime($temp);
        $year2     = date('Y', $timestamp) . '年';
        $month2    = date('m', $timestamp) . '月';
        $day2      = date('d', $timestamp) . '日';

        $previousWeek = my_prev_week($temp);
        $timestamp    = strtotime($previousWeek);
        $year1        = date('Y', $timestamp) . '年';
        $month1       = date('m', $timestamp) . '月';
        $day1         = date('d', $timestamp) . '日';

        $res['weekly'] = $stationName . ' 本周（' . $year1 . $month1 . $day1 . '00点至' . $year2 . $month2 . $day2 . '00点）'
            . '累计发电量 ' . $reportData['real']['week'] . ' 万kWh，'
            . '累计上网电量 ' . $reportData['up']['week'] . ' 万kWh；'
            . '截止' . $month2 . $day2 . '00点，本月累计发电量 ' . $reportData['real']['month'] . ' 万kWh，完成月度发电计划的 ' . $rate['plan']['month'] . '，'
            . '本年累计发电量 ' . $reportData['real']['year'] . ' 万kWh，完成年度发电计划的 ' . $rate['plan']['year'] . '。';

        return $res;
    }
}
