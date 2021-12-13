<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-12-13 22:41:18
 */

namespace App\Controllers;

use App\Models\Meter\MeterLogModel;
use App\Models\Meter\PlanKWhModel;
use App\Models\Meter\RecordModel;
use CodeIgniter\API\ResponseTrait;

class Meters extends BaseController
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

    protected $meterLogModel;
    protected $planKWhModel;

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

        $this->meterLogModel = new MeterLogModel();
        $this->planKWhModel  = new PlanKWhModel();
    }

    public function newRecord()
    {
        // 检查请求数据
        if (!$this->validate('MeterNewRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
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
            $res['error'] = 'invalid meter value';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 取出数据
        $meters = $client['meter'];
        $others = [
            'station_id' => $client['station_id'],
            'log_date'   => $client['log_date'],
            'log_time'   => $client['log_time'],
            'creator'    => $client['creator'],
        ];

        $station_id = $client['station_id'];
        $log_date   = $client['log_date'];
        $log_time   = $client['log_time'];

        $model = new RecordModel();

        // 是否已存在该日期/时间的记录
        $hasLogs = $model->hasSameLogByStationAndDateTime($station_id, $log_date, $log_time);
        if ($hasLogs) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '输入的日期已存在记录';
            return $this->respond($res);
        }

        //
        $result = $model->batchInsert($meters, $others);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '添加一条新记录';
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
        if (!$this->validate('MeterGetRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query['station_id'] = $param['station_id'];
        $query['limit']      = $param['limit'];
        $query['offset']     = $param['offset'];

        $date  = date('Y-m-d', time());
        $model = new RecordModel();

        // 预处理-查询日期
        if ($param['date'] === '') {
            $query2     = ['station_id' => $query['station_id']];
            $columnName = ['log_date'];
            $db         = $model->getLastDateByStation($columnName, $query2);
            if (!isset($db['log_date'])) {
                $res['code'] = EXIT_SUCCESS;
                $res['data'] = ['total' => 0, 'date' => $date, 'data' => []];

                return $this->respond($res);
            }
            $date = $db['log_date'];
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

        // 限定查询日期
        $utils          = service('mixUtils');
        $query['start'] = $utils->getFirstDayOfMonth($date);
        $query['end']   = $utils->getLastDayOfMonth($date);

        $columnName = ['id', 'station_id', 'log_date', 'log_time', 'creator'];
        $result     = $model->getForRecordList($columnName, $query);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['total' => $result['total'], 'date' => $date, 'data' => $result['result']];
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '稍后再试';
        }

        return $this->respond($res);
    }

    public function updateRecord()
    {
        // 检查请求数据
        if (!$this->validate('MeterNewRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
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
            $res['error'] = 'invalid meter value';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
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

        $model  = new RecordModel();
        $result = $model->batchUpdate($meters, $others, $where);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改成功';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    // public function getMetersLogDetail()
    // {
    //     $param = $this->request->getGet();

    //     // 检查请求数据
    //     if (!$this->validate('MeterDailyReportGet')) {
    //         $res['error'] = $this->validator->getErrors();

    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '请求数据无效';
    //         return $this->respond($res);
    //     }

    //     $query['station_id'] = $param['station_id'];
    //     $query['log_date']   = $param['log_date'];
    //     $query['log_time']   = $param['log_time'];

    //     $columnName = ['meter_id', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley'];
    //     $db         = $this->meterLogModel->getByStationDateTime($columnName, $query);

    //     $result = $this->editMetersLogDetail($db);

    //     if (!empty($result)) {
    //         $res['code'] = EXIT_SUCCESS;
    //         $res['data'] = $result;
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '稍后再试';
    //     }

    //     return $this->respond($res);
    // }

    // 2021-11-20
    public function getRecordDetail()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterGetRecordDetail')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query = [
            'station_id' => $param['station_id'],
            'log_date'   => $param['log_date'],
            'log_time'   => $param['log_time'],
        ];

        $model      = new RecordModel();
        $columnName = ['meter_id', 'fak', 'bak', 'frk', 'brk', 'peak', 'valley'];
        $db         = $model->getByStationDateTime($columnName, $query);

        // 指定日期+时间的记录不存在
        // if (empty($db)) {
        //     $utils   = service('mixUtils');
        //     $prevDay = $utils->getPlusOffsetDay($query['log_date'], -1);

        //     $query['log_date'] = $prevDay;

        //     $db = $this->meterLogModel->getByStationDateTime($columnName, $query);
        // }

        // 指定日期前一天的记录不存在，返回data=0
        if (empty($db)) {
            $response['code'] = EXIT_SUCCESS;
            $response['data'] = ['size' => 0, 'record' => []];
            return $this->respond($response);
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

        $response['code'] = EXIT_SUCCESS;
        $response['data'] = ['size' => $cnt, 'record' => $result];

        return $this->respond($response);
    }

    public function getDailyReport()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterDailyReportGet')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 取查询参数
        $station_id = $param['station_id'];
        $log_date   = $param['log_date'];
        $log_time   = $param['log_time'];

        // 查询年计划、月成交量
        $query = [
            'station_id' => $station_id,
            'year'       => date('Y', strtotime($log_date)),
        ];
        $planning = $this->getPlanningForDailyReport($query);

        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];

        if (strpos($log_time, '23:59') !== false) {
            // 发电量delta
            $genEnergy = $this->getGenEnergyForDailyReport($query);

            // 上网电量delta
            $onGridEnergy = $this->getOnGridEnergyForDailyReport($query);

            // 完成率
            $rates = $this->getCompletionRate($log_date, $planning, $genEnergy, $onGridEnergy);

            // 简报
            $stationName = $this->session->get('belongToDeptName');
            $report      = $this->editDailyReports($stationName, $log_date, $rates, $genEnergy, $onGridEnergy);

            // $res['planning']     = $planning;
            // $res['genEnergy']    = $genEnergy;
            // $res['onGridEnergy'] = $onGridEnergy;
            // $res['rates']        = $rates;

            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['data' => $report, 'type' => '23'];

            return $this->respond($res);
        }

        // 针对 20:00，只提取：全机组发电量
        if (strpos($log_time, '20:00') !== false) {
            // 发电量delta
            $genEnergy = $this->getGenEnergyForTwentyClock($query);

            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['data' => $genEnergy, 'type' => '20'];

            return $this->respond($res);
        }

        $res['code'] = EXIT_ERROR;
        return $this->respond($res);
    }

    // 单日数据统计 2021-11-08
    public function getDailyStatistic()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterGetDailyStatistic')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 取查询参数
        $station_id = $param['station_id'];
        // $log_date   = $param['log_date'];
        $log_time = '23:59:00';

        // 查找最近一条23:59记录的日期
        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
        ];
        $columnName = ['log_date'];
        $db         = $this->meterLogModel->getLastDateByStationTime($columnName, $query);
        if (!isset($db['log_date'])) {
            $res['code'] = EXIT_ERROR;
            return $this->respond($res);
        }
        $log_date = $db['log_date'];

        // 查询发电计划、月成交量
        $query = [
            'station_id' => $station_id,
            'date'       => $log_date,
        ];
        $temp           = $this->getPlanKwhAndDealForDailyStatistic($query);
        $totalPlan      = $temp['totalPlan'];
        $totalDeal      = $temp['totalDeal'];
        $curMonthPlan   = $temp['curMonthPlan'];
        $curQuarterPlan = $temp['curQuarterPlan'];
        $curMonthDeal   = $temp['curMonthDeal'];

        // 查询电表数据
        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];
        $produceData   = $this->getProduceDataForDailyStatistic($query);
        $generatorData = $produceData['generatorData'];
        $lineData      = $produceData['lineData'];

        // 计算：完成率
        {
            $rate                  = round(($generatorData['month'] * $this->unitRatio) / ($curMonthPlan * $this->unitRatio), $this->precision);
            $completeMonthPlanRate = strval($rate * 100) . '%';

            $rate                    = round(($generatorData['quarter'] * $this->unitRatio) / ($curQuarterPlan * $this->unitRatio), $this->precision);
            $completeQuarterPlanRate = strval($rate * 100) . '%';

            $rate                 = round(($generatorData['year'] * $this->unitRatio) / ($totalPlan * $this->unitRatio), $this->precision);
            $completeYearPlanRate = strval($rate * 100) . '%';

            $rate                  = round(($lineData['month'] * $this->unitRatio) / ($curMonthDeal * $this->unitRatio), $this->precision);
            $completeMonthDealRate = strval($rate * 100) . '%';
        }

        // 页面显示格式化
        {
            $listData = [
                0 => [
                    'id'               => '1',
                    'rowHeader'        => '今日',
                    'generator'        => $generatorData['day'],
                    'completePlanRate' => '/',
                    'line'             => $lineData['day'],
                    'completeDealRate' => '/',
                ],
                1 => [
                    'id'               => '2',
                    'rowHeader'        => '七日',
                    'generator'        => $generatorData['week'],
                    'completePlanRate' => '/',
                    'line'             => $lineData['week'],
                    'completeDealRate' => '/',
                ],
                2 => [
                    'id'               => '3',
                    'rowHeader'        => '本月',
                    'generator'        => $generatorData['month'],
                    'completePlanRate' => $completeMonthPlanRate,
                    'line'             => $lineData['month'],
                    'completeDealRate' => $completeMonthDealRate,
                ],
                3 => [
                    'id'               => '4',
                    'rowHeader'        => '本季度',
                    'generator'        => $generatorData['quarter'],
                    'completePlanRate' => $completeQuarterPlanRate,
                    'line'             => $lineData['quarter'],
                    'completeDealRate' => '/',
                ],
                4 => [
                    'id'               => '5',
                    'rowHeader'        => '全年',
                    'generator'        => $generatorData['year'],
                    'completePlanRate' => $completeYearPlanRate,
                    'line'             => $lineData['year'],
                    'completeDealRate' => '/',
                ],
                5 => [
                    'id'               => '6',
                    'rowHeader'        => '日高峰',
                    'generator'        => $generatorData['day_peak'],
                    'completePlanRate' => '/',
                    'line'             => '/',
                    'completeDealRate' => '/',
                ],
                6 => [
                    'id'               => '7',
                    'rowHeader'        => '日低谷',
                    'generator'        => $generatorData['day_valley'],
                    'completePlanRate' => '/',
                    'line'             => '/',
                    'completeDealRate' => '/',
                ],
            ];
        }

        $res['code'] = EXIT_SUCCESS;
        // $res['plan']        = $temp;
        // $res['produceData'] = $produceData;
        //
        $res['data'] = [
            'date'      => $log_date . ' ' . $log_time,
            'totalPlan' => $totalPlan,
            'totalDeal' => $totalDeal,
            'dayFrk'    => $generatorData['day_frk'],
            'dayBrk'    => $generatorData['day_brk'],
            'listData'  => $listData,
        ];

        return $this->respond($res);
    }

    // 计划和成交 2021-11-08
    public function getPlanAndDealList()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MetersGetPlanAndDeal')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $date = $param['date'];

        $query['station_id'] = $param['station_id'];
        $query['year']       = substr($date, 0, 4);

        $model = new PlanKWhModel();
        $db    = $model->getByStationYear($query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $db];

        return $this->respond($res);
    }

    // 计划和成交 2021-11-08
    public function updatePlanAndDealRecord()
    {
        // 检查请求数据
        if (!$this->validate('MeterUpdatePlanAndDealRecord')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        if (!is_numeric($client['planning']) || !is_numeric($client['deal'])) {
            $res['error'] = 'invalid planning or deal';

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
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
        $db    = $model->getByStationYearMonth($query);
        if ($id !== $db[0]['id']) {
            $res['error'] = 'conflict with DB';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 修改
        $result = $model->doSave($data);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改一条记录';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    // public function getBasicStatistic()
    // {
    //     $param = $this->request->getGet();

    //     // 检查请求数据
    //     if (!$this->validate('MeterBasicStatisticGet')) {
    //         $res['error'] = $this->validator->getErrors();

    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '请求数据无效';
    //         return $this->respond($res);
    //     }

    //     // 取查询参数
    //     $station_id = $param['station_id'];
    //     // $log_date   = $param['log_date'];
    //     $log_time = '23:59:00';

    //     // 查找最近一条23:59记录的日期
    //     $query = [
    //         'station_id' => $station_id,
    //         'log_time'   => $log_time,
    //     ];
    //     $columnName = ['log_date'];
    //     $db         = $this->meterLogModel->getLastDateByStationTime($columnName, $query);
    //     // if (!isset($db['log_date'])) {
    //     //     $res['code'] = EXIT_SUCCESS;
    //     // }
    //     $log_date = $db['log_date'];

    //     // 查询年计划、月成交量
    //     $query = [
    //         'station_id' => $station_id,
    //         'year'       => date('Y', strtotime($log_date)),
    //     ];
    //     $planning = $this->getPlanningForDailyReport($query);

    //     $query = [
    //         'station_id' => $station_id,
    //         'log_date'   => $log_date,
    //         'log_time'   => $log_time,
    //     ];

    //     // 发电量 delta
    //     $genEnergy = $this->getGenEnergyForDailyReport($query);

    //     // 上网电量 delta
    //     $onGridEnergy = $this->getOnGridEnergyForDailyReport($query);

    //     // 完成率
    //     $rates = $this->getCompletionRate($log_date, $planning, $genEnergy, $onGridEnergy);

    //     // 统计图
    //     // 30天
    //     $thirtyDaysGenEnergy = $this->getThirtyDaysDataForBasicStatistic($query);
    //     // 每月
    //     $monthData = $this->getMonthDataForBasicStatistic($query, $planning['month']);
    //     // 每季度
    //     $quarterData = $this->getQuarterDataForBasicStatistic($query, $planning['quarter']);

    //     $statisticResult = $this->chgUnitForBasicStatistic($rates, $genEnergy, $onGridEnergy, $thirtyDaysGenEnergy, $monthData, $quarterData);

    //     $res['code'] = EXIT_SUCCESS;
    //     $res['data'] = [
    //         'date'           => $log_date . ' ' . $log_time,
    //         'statisticList'  => $statisticResult['statisticList'],
    //         'thirtyDaysData' => $statisticResult['thirtyDaysData'],
    //         'monthData'      => $statisticResult['monthData'],
    //         'quarterData'    => $statisticResult['quarterData'],
    //     ];

    //     return $this->respond($res);
    // }

    public function getOverallStatistic()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterBasicStatisticGet')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 取查询参数
        $station_id = $param['station_id'];
        $log_time   = '23:59:00';

        // 查找最近一条23:59记录的日期
        $meter_ids   = [];
        $meter_ids[] = $this->mainMeterId;
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $columnName = ['log_date', 'meter_id', 'fak'];
        $db3        = $this->meterLogModel->getLastDateByStationTimeMeters($columnName, $query, count($meter_ids));

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

        $db        = $this->getForOverallStatistic($query);
        $yearData  = $db['yearData'];
        $monthData = $db['monthData'];

        $result = $this->chgUnitForOverallStatistic($total, $yearData, $monthData);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = $result;
        // $res['extra'] = $db3;
        // $res['total'] = $total;

        return $this->respond($res);
    }

    public function delMeterLogs()
    {
        // 检查请求数据
        if (!$this->validate('MeterLogsDelete')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        $id         = $client['id'];
        $station_id = $client['station_id'];
        $log_date   = $client['log_date'];
        $log_time   = $client['log_time'];

        // 根据id查找记录
        $db = $this->meterLogModel->getById($id);
        if (!isset($db[0])) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid id';
            return $this->respond($res);
        }
        // 对比
        if ($db[0]['station_id'] !== $station_id || $db[0]['log_date'] !== $log_date || $db[0]['log_time'] !== $log_time || $db[0]['meter_id'] !== '1') {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid record';
            return $this->respond($res);
        }

        // 检查记录时间是不是最近的
        // $query = [
        //     'station_id' => $station_id,
        //     'log_time'   => $log_time,
        // ];
        // $columnName = ['log_date'];
        // $db         = $this->meterLogModel->getLastDateByStationTime($columnName, $query);
        // if ($db['log_date'] !== $log_date) {
        //     $res['code']  = EXIT_ERROR;
        //     $res['msg']   = '不是时间最近的一条记录';
        //     $res['error'] = 'not last record';
        //     return $this->respond($res);
        // }

        // 执行删除，多块电表
        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];
        $result = $this->meterLogModel->deleteByStationDateTime($query);

        if ($result === true) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '已删除';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '删除失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 内部方法
    // 2021-11-08
    protected function getPlanKwhAndDealForDailyStatistic($query)
    {
        // 初值0
        $result = [
            'curMonthPlan'   => '0',
            'curQuarterPlan' => '0',
            'totalPlan'      => '0',
            //
            'curMonthDeal'   => '0',
            'totalDeal'      => '0',
        ];

        if (empty($query['date']) || empty($query['station_id'])) {
            return $result;
        }

        // 查询db / kwh
        $query2 = [
            'year'       => date('Y', strtotime($query['date'])),
            'station_id' => $query['station_id'],
        ];
        $model = new PlanKWhModel();
        $db    = $model->getByStationYear($query2);
        if (empty($db)) {
            return $result;
        }

        $month   = date('n', strtotime($query['date']));
        $quarter = ceil($month / 3);

        // 计算 / kwh
        $quarters[0] = intval($db[0]['planning']) + intval($db[1]['planning']) + intval($db[2]['planning']);
        $quarters[1] = intval($db[3]['planning']) + intval($db[4]['planning']) + intval($db[5]['planning']);
        $quarters[2] = intval($db[6]['planning']) + intval($db[7]['planning']) + intval($db[8]['planning']);
        $quarters[3] = intval($db[9]['planning']) + intval($db[10]['planning']) + intval($db[11]['planning']);

        $curQuarterPlan = $quarters[$quarter - 1];
        //
        $totalPlan    = 0;
        $totalDeal    = 0;
        $curMonthPlan = 0;
        $curMonthDeal = 0;

        $cnt = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $totalPlan = $totalPlan + intval($db[$i]['planning']);
            $totalDeal = $totalDeal + intval($db[$i]['deal']);
            //
            if ($db[$i]['month'] == $month) {
                $curMonthPlan = intval($db[$i]['planning']);
                $curMonthDeal = intval($db[$i]['deal']);
            }
        }

        // 单位 kwh -> 万kwh
        $result['totalPlan']      = strval(round($totalPlan / $this->unitRatio, $this->precision));
        $result['totalDeal']      = strval(round($totalDeal / $this->unitRatio, $this->precision));
        $result['curMonthPlan']   = strval(round($curMonthPlan / $this->unitRatio, $this->precision));
        $result['curQuarterPlan'] = strval(round($curQuarterPlan / $this->unitRatio, $this->precision));
        $result['curMonthDeal']   = strval(round($curMonthDeal / $this->unitRatio, $this->precision));

        return $result;
    }

    // 2021-11-08
    protected function getProduceDataForDailyStatistic($query)
    {
        // 发电机电表数据
        $generatorData = [
            'day'        => '0',
            'week'       => '0',
            'month'      => '0',
            'quarter'    => '0',
            'year'       => '0',
            'day_peak'   => '0',
            'day_valley' => '0',
            'day_frk'    => '0',
            'day_brk'    => '0',
        ];

        // 线路主表数据
        $lineData = [
            'day'     => '0',
            'week'    => '0',
            'month'   => '0',
            'quarter' => '0',
            'year'    => '0',
        ];

        $result = [
            'generatorData' => $generatorData,
            'lineData'      => $lineData,
        ];

        if (empty($query['station_id']) || empty($query['log_date']) || empty($query['log_time'])) {
            return $result;
        }

        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 查询条件：日期
        $utils = service('mixUtils');

        $today       = $log_date;
        $prevDay     = $utils->getPlusOffsetDay($log_date, -1);
        $prevWeek    = $utils->getDayOfPreviousWeek($log_date);
        $prevMonth   = $utils->getLastDayOfMonth($log_date, -1);
        $prevQuarter = $utils->getLastDayOfQuarter($log_date, -1);
        $prevYear    = $utils->getLastDayOfYear($log_date, -1);
        $dates       = [$today, $prevDay, $prevWeek, $prevMonth, $prevQuarter, $prevYear];

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        // 发电机电表：查询 kwh
        $columnName = ['log_date', 'fak', 'frk', 'brk', 'peak', 'valley'];
        $query2     = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];

        $model = new RecordModel();
        $db    = $model->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $result;
        }

        // 发电机电表：求和  kwh
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            // 日期维度
            $date   = $dates[$i];
            $fak    = 0;
            $frk    = 0;
            $brk    = 0;
            $peak   = 0;
            $valley = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                // 电表维度
                if ($db[$j]['log_date'] === $date) {
                    $fak    = $fak + intval($db[$j]['fak']);
                    $frk    = $frk + intval($db[$j]['frk']);
                    $brk    = $brk + intval($db[$j]['brk']);
                    $peak   = $peak + intval($db[$j]['peak']);
                    $valley = $valley + intval($db[$j]['valley']);
                }
            }
            $db2[$date] = [
                'fak'    => $fak,
                'frk'    => $frk,
                'brk'    => $brk,
                'peak'   => $peak,
                'valley' => $valley,
            ];
        }

        // 发电机电表：单日 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $delta                = ($db2[$today]['fak'] - $db2[$prevDay]['fak']) * $this->genMeterRatio;
            $value                = round($delta / $this->unitRatio, $this->precision);
            $generatorData['day'] = strval($value);
        }

        if (($db2[$today]['frk'] > $db2[$prevDay]['frk']) && ($db2[$prevDay]['frk'] > 0)) {
            $delta                    = ($db2[$today]['frk'] - $db2[$prevDay]['frk']) * $this->genMeterRatio;
            $value                    = round($delta / $this->unitRatio, $this->precision);
            $generatorData['day_frk'] = strval($value);
        }
        if (($db2[$today]['brk'] > $db2[$prevDay]['brk']) && ($db2[$prevDay]['brk'] > 0)) {
            $delta                    = ($db2[$today]['brk'] - $db2[$prevDay]['brk']) * $this->genMeterRatio;
            $value                    = round($delta / $this->unitRatio, $this->precision);
            $generatorData['day_brk'] = strval($value);
        }

        if (($db2[$today]['peak'] > $db2[$prevDay]['peak']) && ($db2[$prevDay]['peak'] > 0)) {
            $delta                     = ($db2[$today]['peak'] - $db2[$prevDay]['peak']) * $this->genMeterRatio;
            $value                     = round($delta / $this->unitRatio, $this->precision);
            $generatorData['day_peak'] = strval($value);
        }
        if (($db2[$today]['valley'] > $db2[$prevDay]['valley']) && ($db2[$prevDay]['valley'] > 0)) {
            $delta                       = ($db2[$today]['valley'] - $db2[$prevDay]['valley']) * $this->genMeterRatio;
            $value                       = round($delta / $this->unitRatio, $this->precision);
            $generatorData['day_valley'] = strval($value);
        }

        // 发电机电表：七日 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevWeek]['fak']) && ($db2[$prevWeek]['fak'] > 0)) {
            $delta                 = ($db2[$today]['fak'] - $db2[$prevWeek]['fak']) * $this->genMeterRatio;
            $value                 = round($delta / $this->unitRatio, $this->precision);
            $generatorData['week'] = strval($value);
        }

        // 发电机电表：月 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevMonth]['fak']) && ($db2[$prevMonth]['fak'] > 0)) {
            $delta                  = ($db2[$today]['fak'] - $db2[$prevMonth]['fak']) * $this->genMeterRatio;
            $value                  = round($delta / $this->unitRatio, $this->precision);
            $generatorData['month'] = strval($value);
        }

        // 发电机电表：季度 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevQuarter]['fak']) && ($db2[$prevQuarter]['fak'] > 0)) {
            $delta                    = ($db2[$today]['fak'] - $db2[$prevQuarter]['fak']) * $this->genMeterRatio;
            $value                    = round($delta / $this->unitRatio, $this->precision);
            $generatorData['quarter'] = strval($value);
        }

        // 发电机电表：年 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevYear]['fak']) && ($db2[$prevYear]['fak'] > 0)) {
            $delta                 = ($db2[$today]['fak'] - $db2[$prevYear]['fak']) * $this->genMeterRatio;
            $value                 = round($delta / $this->unitRatio, $this->precision);
            $generatorData['year'] = strval($value);
        }

        $result['generatorData'] = $generatorData;

        // 线路主表：查询 kwh
        $columnName = ['log_date', 'fak'];
        $query2     = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $this->mainMeterId,
        ];
        $model = new RecordModel();
        $db    = $model->getByStationDatesTimeMeter($columnName, $query2);
        if (empty($db)) {
            return $result;
        }

        // 线路主表：kwh
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($db[$j]['log_date'] === $date) {
                    $fak = intval($db[$j]['fak']);
                }
            }
            $db2[$date] = ['fak' => $fak];
        }

        // 线路主表：单日 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $delta           = ($db2[$today]['fak'] - $db2[$prevDay]['fak']) * $this->mainMeterRatio;
            $value           = round($delta / $this->unitRatio, $this->precision);
            $lineData['day'] = strval($value);
        }

        // 线路主表：七日 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevWeek]['fak']) && ($db2[$prevWeek]['fak'] > 0)) {
            $delta            = ($db2[$today]['fak'] - $db2[$prevWeek]['fak']) * $this->mainMeterRatio;
            $value            = round($delta / $this->unitRatio, $this->precision);
            $lineData['week'] = strval($value);
        }

        // 线路主表：月 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevMonth]['fak']) && ($db2[$prevMonth]['fak'] > 0)) {
            $delta             = ($db2[$today]['fak'] - $db2[$prevMonth]['fak']) * $this->mainMeterRatio;
            $value             = round($delta / $this->unitRatio, $this->precision);
            $lineData['month'] = strval($value);
        }

        // 线路主表：季度 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevQuarter]['fak']) && ($db2[$prevQuarter]['fak'] > 0)) {
            $delta               = ($db2[$today]['fak'] - $db2[$prevQuarter]['fak']) * $this->mainMeterRatio;
            $value               = round($delta / $this->unitRatio, $this->precision);
            $lineData['quarter'] = strval($value);
        }

        // 线路主表：年 / 电表倍率、单位 kwh -> 万kwh
        if (($db2[$today]['fak'] > $db2[$prevYear]['fak']) && ($db2[$prevYear]['fak'] > 0)) {
            $delta            = ($db2[$today]['fak'] - $db2[$prevYear]['fak']) * $this->mainMeterRatio;
            $value            = round($delta / $this->unitRatio, $this->precision);
            $lineData['year'] = strval($value);
        }

        $result['lineData'] = $lineData;

        return $result;
    }

    protected function getPlanningForDailyReport($query)
    {
        // 初值0
        $totalMonthOfYear = 12;
        $temp             = [];
        for ($i = 0; $i < $totalMonthOfYear; $i++) {
            $temp[$i] = [
                'id'       => 0,
                'month'    => ($i + 1) . '月',
                'planning' => 0,
                'real'     => 0,
            ];
        }
        $quarter[0] = 0;
        $quarter[1] = 0;
        $quarter[2] = 0;
        $quarter[3] = 0;

        //
        $planning = $this->planKWhModel->getByStationYear($query);

        if (empty($planning) || count($planning) !== $totalMonthOfYear) {
            return [
                'month'          => $temp,
                'planning_total' => 0,
                'quarter'        => $quarter,
            ];
        }

        $planning_total = 0;
        $cnt            = count($planning);
        for ($i = 0; $i < $cnt; $i++) {
            $planning[$i]['month'] = $planning[$i]['month'] . '月';
            $planning_total        = $planning_total + $planning[$i]['planning'];
        }

        $quarter[0] = $planning[0]['planning'] + $planning[1]['planning'] + $planning[2]['planning'];
        $quarter[1] = $planning[3]['planning'] + $planning[4]['planning'] + $planning[5]['planning'];
        $quarter[2] = $planning[6]['planning'] + $planning[7]['planning'] + $planning[8]['planning'];
        $quarter[3] = $planning[9]['planning'] + $planning[10]['planning'] + $planning[11]['planning'];

        return [
            'month'          => $planning,
            'planning_total' => $planning_total,
            'quarter'        => $quarter,
        ];

    }

    protected function getGenEnergyForDailyReport($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 初值0
        $res = [
            'day'        => 0,
            'week'       => 0,
            'month'      => 0,
            'quarter'    => 0,
            'year'       => 0,
            'day_peak'   => 0,
            'day_valley' => 0,
        ];

        // 需查找的日期
        $utils = service('mixUtils');

        $today       = $log_date;
        $prevDay     = $utils->getPlusOffsetDay($log_date, -1);
        $prevWeek    = $utils->getDayOfPreviousWeek($log_date);
        $prevMonth   = $utils->getLastDayOfMonth($log_date, -1);
        $prevQuarter = $utils->getLastDayOfQuarter($log_date, -1);
        $prevYear    = $utils->getLastDayOfYear($log_date, -1);
        $dates       = [$today, $prevDay, $prevWeek, $prevMonth, $prevQuarter, $prevYear];

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        // 查询给定日期：表读数，峰谷电量
        $columnName = ['log_date', 'fak', 'peak', 'valley'];
        $query2     = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $db = $this->meterLogModel->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date   = $dates[$i];
            $fak    = 0;
            $peak   = 0;
            $valley = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($db[$j]['log_date'] === $date) {
                    $fak += $db[$j]['fak'];
                    $peak += $db[$j]['peak'];
                    $valley += $db[$j]['valley'];
                }
            }
            $db2[$date] = [
                'fak'    => $fak,
                'peak'   => $peak,
                'valley' => $valley,
            ];
        }

        // 日
        if (($db2[$today]['fak'] > $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $res['day'] = $db2[$today]['fak'] - $db2[$prevDay]['fak'];
        }
        if (($db2[$today]['peak'] > $db2[$prevDay]['peak']) && ($db2[$prevDay]['peak'] > 0)) {
            $res['day_peak'] = $db2[$today]['peak'] - $db2[$prevDay]['peak'];
        }
        if (($db2[$today]['valley'] > $db2[$prevDay]['valley']) && ($db2[$prevDay]['valley'] > 0)) {
            $res['day_valley'] = $db2[$today]['valley'] - $db2[$prevDay]['valley'];
        }

        // 七天
        if (($db2[$today]['fak'] > $db2[$prevWeek]['fak']) && ($db2[$prevWeek]['fak'] > 0)) {
            $res['week'] = $db2[$today]['fak'] - $db2[$prevWeek]['fak'];
        }

        // 月
        if (($db2[$today]['fak'] > $db2[$prevMonth]['fak']) && ($db2[$prevMonth]['fak'] > 0)) {
            $res['month'] = $db2[$today]['fak'] - $db2[$prevMonth]['fak'];
        }

        // 季度
        if (($db2[$today]['fak'] > $db2[$prevQuarter]['fak']) && ($db2[$prevQuarter]['fak'] > 0)) {
            $res['quarter'] = $db2[$today]['fak'] - $db2[$prevQuarter]['fak'];
        }

        // 年
        if (($db2[$today]['fak'] > $db2[$prevYear]['fak']) && ($db2[$prevYear]['fak'] > 0)) {
            $res['year'] = $db2[$today]['fak'] - $db2[$prevYear]['fak'];
        }

        return $res;
    }

    protected function getGenEnergyForTwentyClock($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 初值0
        $res = 0;

        // 需查找的日期
        $utils = service('mixUtils');

        $today   = $log_date;
        $prevDay = $utils->getPlusOffsetDay($log_date, -1);
        $dates   = [$today, $prevDay];

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        // 查询给定日期：表读数，正向有功
        $columnName = ['log_date', 'fak'];
        $query2     = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $db = $this->meterLogModel->getByStationDatesTimeMeters($columnName, $query2);
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
        if (($db2[$today]['fak'] > $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $value = $db2[$today]['fak'] - $db2[$prevDay]['fak'];
        }

        $precision = 4;

        // 电表倍率，单位 kWh -> 万kWh
        $res = round($value * $this->genMeterRatio / 10000, $precision);

        return $res;
    }

    protected function getOnGridEnergyForDailyReport($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        // 初值0
        $res = [
            'day'     => 0,
            'week'    => 0,
            'month'   => 0,
            'quarter' => 0,
            'year'    => 0,
        ];

        // 需查找的日期
        $utils = service('mixUtils');

        $today       = $log_date;
        $prevDay     = $utils->getPlusOffsetDay($log_date, -1);
        $prevWeek    = $utils->getDayOfPreviousWeek($log_date);
        $prevMonth   = $utils->getLastDayOfMonth($log_date, -1);
        $prevQuarter = $utils->getLastDayOfQuarter($log_date, -1);
        $prevYear    = $utils->getLastDayOfYear($log_date, -1);
        $dates       = [$today, $prevDay, $prevWeek, $prevMonth, $prevQuarter, $prevYear];

        // 查询给定日期：日电量，表读数
        $columnName = ['log_date', 'fak'];
        $query2     = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $this->mainMeterId,
        ];
        $db = $this->meterLogModel->getByStationDatesTimeMeter($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($db[$j]['log_date'] === $date) {
                    $fak = $db[$j]['fak'];
                }
            }
            $db2[$date] = [
                'fak' => $fak,
            ];
        }

        // 日
        if (($db2[$today]['fak'] > $db2[$prevDay]['fak']) && ($db2[$prevDay]['fak'] > 0)) {
            $res['day'] = $db2[$today]['fak'] - $db2[$prevDay]['fak'];
        }

        // 七天
        if (($db2[$today]['fak'] > $db2[$prevWeek]['fak']) && ($db2[$prevWeek]['fak'] > 0)) {
            $res['week'] = $db2[$today]['fak'] - $db2[$prevWeek]['fak'];
        }

        // 月
        if (($db2[$today]['fak'] > $db2[$prevMonth]['fak']) && ($db2[$prevMonth]['fak'] > 0)) {
            $res['month'] = $db2[$today]['fak'] - $db2[$prevMonth]['fak'];
        }

        // 季度
        if (($db2[$today]['fak'] > $db2[$prevQuarter]['fak']) && ($db2[$prevQuarter]['fak'] > 0)) {
            $res['quarter'] = $db2[$today]['fak'] - $db2[$prevQuarter]['fak'];
        }

        // 年
        if (($db2[$today]['fak'] > $db2[$prevYear]['fak']) && ($db2[$prevYear]['fak'] > 0)) {
            $res['year'] = $db2[$today]['fak'] - $db2[$prevYear]['fak'];
        }

        return $res;
    }

    protected function getThirtyDaysDataForBasicStatistic($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils    = service('mixUtils');
        $length   = 30;
        $start_at = $utils->getPlusOffsetDay($log_date, -$length);
        $end_at   = $log_date;

        // 初值0
        $res      = [];
        $dates[0] = $utils->getPlusOffsetDay($log_date, -$length);
        for ($i = 0; $i < $length; $i++) {
            $date    = $utils->getPlusOffsetDay($log_date, -($length - 1 - $i));
            $res[$i] = [
                'date' => $date,
                'real' => 0,
            ];
            $dates[$i + 1] = $date;
        }

        $query2 = [
            'station_id'     => $station_id,
            'start_at'       => $start_at,
            'end_at'         => $end_at,
            'log_time'       => $log_time,
            'first_meter_id' => $this->firstGenMeterId,
            'last_meter_id'  => $this->lastGenMeterId,
        ];
        $columnName = ['log_date, fak'];
        $db         = $this->meterLogModel->getByStationDateRangeTimeMeters($columnName, $query2);
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

        // delta
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($db2[$b]['fak'] > $db2[$a]['fak']) && ($db2[$a]['fak'] > 0)) {
                $res[$i]['real'] = $db2[$b]['fak'] - $db2[$a]['fak'];
            }
        }

        return $res;
    }

    protected function getMonthDataForBasicStatistic($query, $planning)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils  = service('mixUtils');
        $length = 12;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'month'    => ($i + 1) . '月',
                'planning' => $planning[$i]['planning'],
                'real'     => 0,
            ];
        }

        // 每月的最后一天的日期
        $month = date('n', strtotime($log_date));
        $dates = [];
        for ($i = 0; $i < $month; $i++) {
            $dates[] = $utils->getLastDayOfMonth($log_date, (-$month + $i));
        }
        $dates[] = $log_date;

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $columnName = ['log_date, fak'];
        $db         = $this->meterLogModel->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // sum
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $temp = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($dates[$i] === $db[$j]['log_date']) {
                    $temp = $temp + $db[$j]['fak'];
                }
            }
            $db2[] = ['date' => $dates[$i], 'real' => $temp];
        }

        // delta
        $cnt = count($db2);
        for ($i = 0; $i < ($cnt - 1); $i++) {
            if (($db2[$i + 1]['real'] > $db2[$i]['real']) && ($db2[$i]['real'] > 0)) {
                $res[$i]['real'] = $db2[$i + 1]['real'] - $db2[$i]['real'];
            }
        }

        return $res;

    }

    protected function getQuarterDataForBasicStatistic($query, $planning)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils  = service('mixUtils');
        $length = 4;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'quarter'  => ($i + 1) . '季度',
                'planning' => $planning[$i],
                'real'     => 0,
            ];
        }

        // 每季度的最后一天的日期
        $quarter = ceil((date('n', strtotime($log_date))) / 3);
        $dates   = [];
        for ($i = 0; $i < $quarter; $i++) {
            $dates[] = $utils->getLastDayOfQuarter($log_date, (-$quarter + $i));
        }
        $dates[] = $log_date;

        $meter_ids = [];
        for ($i = $this->firstGenMeterId; $i <= $this->lastGenMeterId; $i++) {
            $meter_ids[] = $i;
        }

        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $dates,
            'log_time'   => $log_time,
            'meter_id'   => $meter_ids,
        ];
        $columnName = ['log_date, fak'];
        $db         = $this->meterLogModel->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // sum
        $db2  = [];
        $cnt  = count($dates);
        $cnt2 = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $temp = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                if ($dates[$i] === $db[$j]['log_date']) {
                    $temp = $temp + $db[$j]['fak'];
                }
            }
            $db2[] = ['date' => $dates[$i], 'real' => $temp];
        }

        // delta
        $cnt = count($db2);
        for ($i = 0; $i < ($cnt - 1); $i++) {
            if (($db2[$i + 1]['real'] > $db2[$i]['real']) && ($db2[$i]['real'] > 0)) {
                $res[$i]['real'] = $db2[$i + 1]['real'] - $db2[$i]['real'];
            }
        }

        return $res;
    }

    protected function getCompletionRate($date, $planning, $genEnergy, $onGridEnergy)
    {
        if (empty($planning) || empty($genEnergy) || empty($onGridEnergy) || empty($date)) {
            return false;
        }

        $month   = date('n', strtotime($date));
        $quarter = ceil($month / 3);

        // 初值0
        $res = [
            'month'      => '0%',
            'month_deal' => '0%',
            'quarter'    => '0%',
            'year'       => '0%',
        ];
        $rate      = 0;
        $precision = 4;

        // 数据库中，电表读数没有乘以表倍率，单位kWh；计划电量页面输入数据单位万kWh，4为小数，入库前乘以10000，取整数部分，单位kWh

        // 月计划完成率：机组 / 月计划发电量
        if (!empty($planning['month'][$month - 1]['planning'])) {
            $rate         = round($genEnergy['month'] * $this->genMeterRatio / $planning['month'][$month - 1]['planning'], $precision);
            $res['month'] = ($rate * 100) . '%';
        }

        // 月成交完成率：线路上网 / 月成交电量
        if (!empty($planning['month'][$month - 1]['deal'])) {
            $rate              = round($onGridEnergy['month'] * $this->mainMeterRatio / $planning['month'][$month - 1]['deal'], $precision);
            $res['month_deal'] = ($rate * 100) . '%';
        }

        // 季度计划完成率：机组 / 季度计划发电量
        if (!empty($planning['quarter'][$quarter - 1])) {
            $rate           = round($genEnergy['quarter'] * $this->genMeterRatio / $planning['quarter'][$quarter - 1], $precision);
            $res['quarter'] = ($rate * 100) . '%';
        }

        // 年度计划完成率：机组 / 年度计划发电量
        if (!empty($planning['planning_total'])) {
            $rate        = round($genEnergy['year'] * $this->genMeterRatio / $planning['planning_total'], $precision);
            $res['year'] = ($rate * 100) . '%';
        }

        return $res;

    }

    protected function editDailyReports($stationName, $date, $rates, $genEnergy, $onGridEnergy)
    {
        if (empty($stationName) || empty($date) || empty($rates) || empty($genEnergy) || empty($onGridEnergy)) {
            return false;
        }

        $res = [
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

        $precision = 4;

        // 电表倍率，单位 kWh -> 万kWh
        $dayGenEnergy     = round($genEnergy['day'] * $this->genMeterRatio / 10000, $precision);
        $monthGenEnergy   = round($genEnergy['month'] * $this->genMeterRatio / 10000, $precision);
        $quarterGenEnergy = round($genEnergy['quarter'] * $this->genMeterRatio / 10000, $precision);
        $yearGenEnergy    = round($genEnergy['year'] * $this->genMeterRatio / 10000, $precision);

        $res['daily1'] = $stationName . ' '
            . $year . $month . $day . '，发电量为 ' . $dayGenEnergy . ' 万kWh。'
            . '截止' . $month . $day . '24时，电厂本月完成发电量 ' . $monthGenEnergy . ' 万kWh，完成月度发电计划的 ' . $rates['month'] . '，'
            . $quarter . '季度累计完成发电量 ' . $quarterGenEnergy . ' 万kWh，完成季度发电计划的 ' . $rates['quarter'] . '，'
            . '本年累计完成发电量 ' . $yearGenEnergy . ' 万kWh，完成年度发电计划的 ' . $rates['year'] . '。'
            . '本日弃水电量为 0 万kWh，本月弃水电量累计 0 万kWh。';

        //
        $dayOnGridEnergy   = round($onGridEnergy['day'] * $this->mainMeterRatio / 10000, $precision);
        $monthOnGridEnergy = round($onGridEnergy['month'] * $this->mainMeterRatio / 10000, $precision);
        $yearOnGridEnergy  = round($onGridEnergy['year'] * $this->mainMeterRatio / 10000, $precision);

        $res['daily2'] = $stationName . '：'
            . $year . $month . $day . '发电机组为#1、#2、#3机组，出力在 0MW 到 161MW 之间，'
            . '机组日实际发电量为 ' . $dayGenEnergy . ' 万kWh，'
            . '日上网电量为 ' . $dayOnGridEnergy . ' 万kWh。'
            . '截止' . $month . $day . '24时，电厂本月完成上网电量 ' . $monthOnGridEnergy . ' 万kWh，完成月度成交电量的 ' . $rates['month_deal'] . '，'
            . '本年完成上网电量 ' . $yearOnGridEnergy . ' 万kWh。'
            . '本日弃水电量为 0 万kWh，本月弃水电量累计 0 万kWh。';

        //
        $utils = service('mixUtils');

        $temp      = $utils->getPlusOffsetDay($date, 1);
        $timestamp = strtotime($temp);
        $year2     = date('Y', $timestamp) . '年';
        $month2    = date('m', $timestamp) . '月';
        $day2      = date('d', $timestamp) . '日';

        $previousWeek = $utils->getDayOfPreviousWeek($temp);
        // $temp         = $utils->getPlusOffsetDay($previousWeek);
        $timestamp = strtotime($previousWeek);
        $year1     = date('Y', $timestamp) . '年';
        $month1    = date('m', $timestamp) . '月';
        $day1      = date('d', $timestamp) . '日';

        $weekGenEnergy    = round($genEnergy['week'] * $this->genMeterRatio / 10000, $precision);
        $weekOnGirdEnergy = round($onGridEnergy['week'] * $this->mainMeterRatio / 10000, $precision);

        $res['weekly'] = $stationName . '本周(' . $year1 . $month1 . $day1 . '00点至' . $year2 . $month2 . $day2 . '00点)'
            . '发电量为 ' . $weekGenEnergy . ' 万kWh，'
            . '上网电量为 ' . $weekOnGirdEnergy . ' 万kWh；'
            . '截止' . $month2 . $day2 . '00点，本月累计发电量为 ' . $monthGenEnergy . ' 万kWh，完成月度发电计划的 ' . $rates['month'] . '，'
            . '本年度发电量为 ' . $yearGenEnergy . ' 万kWh，完成年度发电计划的 ' . $rates['year'] . '。';

        return $res;
    }

    protected function chgUnitForBasicStatistic($rates, $genEnergy, $onGridEnergy, $thirtyDaysGenEnergy, $monthData, $quarterData)
    {
        if (empty($rates) || empty($genEnergy) || empty($onGridEnergy) || empty($thirtyDaysGenEnergy) || empty($monthData) || empty($quarterData)) {
            return false;
        }

        $precision = 4;

        // 电表倍率，单位换算
        $dayGenEnergy       = round($genEnergy['day'] * $this->genMeterRatio / 10000, $precision);
        $weekGenEnergy      = round($genEnergy['week'] * $this->genMeterRatio / 10000, $precision);
        $monthGenEnergy     = round($genEnergy['month'] * $this->genMeterRatio / 10000, $precision);
        $quarterGenEnergy   = round($genEnergy['quarter'] * $this->genMeterRatio / 10000, $precision);
        $yearGenEnergy      = round($genEnergy['year'] * $this->genMeterRatio / 10000, $precision);
        $dayPeakGenEnergy   = round($genEnergy['day_peak'] * $this->genMeterRatio / 10000, $precision);
        $dayValleyGenEnergy = round($genEnergy['day_valley'] * $this->genMeterRatio / 10000, $precision);

        // 上网，电表倍率，单位换算
        $dayOnGridEnergy     = round($onGridEnergy['day'] * $this->mainMeterRatio / 10000, $precision);
        $weekOnGridEnergy    = round($onGridEnergy['week'] * $this->mainMeterRatio / 10000, $precision);
        $monthOnGridEnergy   = round($onGridEnergy['month'] * $this->mainMeterRatio / 10000, $precision);
        $quarterOnGridEnergy = round($onGridEnergy['quarter'] * $this->mainMeterRatio / 10000, $precision);
        $yearOnGridEnergy    = round($onGridEnergy['year'] * $this->mainMeterRatio / 10000, $precision);

        $statisticList = [
            0 => [
                'id'        => '1',
                'rowHeader' => '今日',
                'onGrid'    => $dayOnGridEnergy,
                'genEnergy' => $dayGenEnergy,
                'rate'      => '/',
            ],
            1 => [
                'id'        => '2',
                'rowHeader' => '七日',
                'onGrid'    => $weekOnGridEnergy,
                'genEnergy' => $weekGenEnergy,
                'rate'      => '/',
            ],
            2 => [
                'id'        => '3',
                'rowHeader' => '本月',
                'onGrid'    => $monthOnGridEnergy,
                'genEnergy' => $monthGenEnergy,
                'rate'      => $rates['month'],
            ],
            3 => [
                'id'        => '4',
                'rowHeader' => '本季度',
                'onGrid'    => $quarterOnGridEnergy,
                'genEnergy' => $quarterGenEnergy,
                'rate'      => $rates['quarter'],
            ],
            4 => [
                'id'        => '5',
                'rowHeader' => '全年',
                'onGrid'    => $yearOnGridEnergy,
                'genEnergy' => $yearGenEnergy,
                'rate'      => $rates['year'],
            ],
            5 => [
                'id'        => '6',
                'rowHeader' => '日高峰',
                'onGrid'    => '/',
                'genEnergy' => $dayPeakGenEnergy,
                'rate'      => '/',
            ],
            6 => [
                'id'        => '7',
                'rowHeader' => '日低谷',
                'onGrid'    => '/',
                'genEnergy' => $dayValleyGenEnergy,
                'rate'      => '/',
            ],
        ];

        // 电表倍率，单位换算 kWh -> 万kWh
        $cnt = count($thirtyDaysGenEnergy);
        for ($i = 0; $i < $cnt; $i++) {
            $thirtyDaysGenEnergy[$i]['real'] = round($thirtyDaysGenEnergy[$i]['real'] * $this->genMeterRatio / 10000, $precision);
        }

        $cnt = count($monthData);
        for ($i = 0; $i < $cnt; $i++) {
            $monthData[$i]['planning'] = round($monthData[$i]['planning'] / 10000, $precision);
            $monthData[$i]['real']     = round($monthData[$i]['real'] * $this->genMeterRatio / 10000, $precision);
        }

        $cnt = count($quarterData);
        for ($i = 0; $i < $cnt; $i++) {
            $quarterData[$i]['planning'] = round($quarterData[$i]['planning'] / 10000, $precision);
            $quarterData[$i]['real']     = round($quarterData[$i]['real'] * $this->genMeterRatio / 10000, $precision);
        }

        return [
            'statisticList'  => $statisticList,
            'thirtyDaysData' => $thirtyDaysGenEnergy,
            'monthData'      => $monthData,
            'quarterData'    => $quarterData,
        ];
    }

    protected function getForOverallStatistic($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];
        $meter_id   = $query['meter_id'];

        $utils = service('mixUtils');

        // 每月最后一天的日期
        $first_at = $this->first_at;
        $month    = [];
        $month[]  = $first_at;

        $target   = $utils->getLastDayOfMonth($log_date);
        $i        = 1;
        $continue = true;
        do {
            $temp = $utils->getLastDayOfMonth($first_at, $i);
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

        $target   = $utils->getLastDayOfYear($log_date);
        $i        = 1;
        $continue = true;
        do {
            $temp = $utils->getLastDayOfYear($first_at, $i);
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
        $query2 = [
            'station_id' => $station_id,
            'log_date'   => $month,
            'log_time'   => $log_time,
            'meter_id'   => $meter_id,
        ];
        $columnName = ['log_date, fak'];
        $db         = $this->meterLogModel->getByStationDatesTimeMeter($columnName, $query2);
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

    protected function chgUnitForOverallStatistic($total, $yearData, $monthData)
    {
        $precision = 4;

        // 上网，电表倍率，单位换算 kWh -> 万kWh
        $total['onGridEnergy'] = round($total['onGridEnergy'] * $this->mainMeterRatio / 10000, $precision);
        $total['genEnergy']    = round($total['genEnergy'] * $this->genMeterRatio / 10000, $precision);

        $cnt = count($yearData);
        for ($i = 0; $i < $cnt; $i++) {
            $yearData[$i]['value'] = round($yearData[$i]['value'] * $this->mainMeterRatio / 10000, $precision);
        }

        $cnt = count($monthData);
        for ($i = 0; $i < $cnt; $i++) {
            $monthData[$i]['value'] = round($monthData[$i]['value'] * $this->mainMeterRatio / 10000, $precision);
        }

        return ['total' => $total, 'yearData' => $yearData, 'monthData' => $monthData];

    }

    // protected function editMetersLogDetail($db)
    // {
    //     if (empty($db)) {
    //         return false;
    //     }

    //     // 初值
    //     $res['tab1Data'] = [
    //         ['id' => 1, 'rowHeader' => '正向有功', 'value1' => '/', 'value2' => '/'],
    //         ['id' => 2, 'rowHeader' => '反向有功', 'value1' => '/', 'value2' => '/'],
    //         ['id' => 3, 'rowHeader' => '正向无功', 'value1' => '/', 'value2' => '/'],
    //         ['id' => 4, 'rowHeader' => '反向无功', 'value1' => '/', 'value2' => '/'],
    //         ['id' => 5, 'rowHeader' => '高峰', 'value1' => '/', 'value2' => '/'],
    //         ['id' => 6, 'rowHeader' => '低谷', 'value1' => '/', 'value2' => '/'],
    //     ];

    //     $res['tab2Data'] = [
    //         ['id' => 1, 'rowHeader' => '正向有功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 2, 'rowHeader' => '反向有功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 3, 'rowHeader' => '正向无功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 4, 'rowHeader' => '反向无功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 5, 'rowHeader' => '高峰', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 6, 'rowHeader' => '低谷', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //     ];

    //     $res['tab3Data'] = [
    //         ['id' => 1, 'rowHeader' => '正向有功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 2, 'rowHeader' => '反向有功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 3, 'rowHeader' => '正向无功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 4, 'rowHeader' => '反向无功', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 5, 'rowHeader' => '高峰', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //         ['id' => 6, 'rowHeader' => '低谷', 'value1' => '/', 'value2' => '/', 'value3' => '/'],
    //     ];

    //     $res['tab4Data'] = [
    //         ['id' => 1, 'rowHeader' => '正向有功', 'value1' => '/'],
    //         ['id' => 2, 'rowHeader' => '反向有功', 'value1' => '/'],
    //         ['id' => 3, 'rowHeader' => '正向无功', 'value1' => '/'],
    //         ['id' => 4, 'rowHeader' => '反向无功', 'value1' => '/'],
    //         ['id' => 5, 'rowHeader' => '高峰', 'value1' => '/'],
    //         ['id' => 6, 'rowHeader' => '低谷', 'value1' => '/'],
    //     ];

    //     //
    //     $cnt = count($db);
    //     for ($i = 0; $i < $cnt; $i++) {
    //         if ($db[$i]['meter_id'] === '1') {
    //             $res['tab1Data'][0]['value1'] = $db[$i]['fak'] / 10000;
    //             $res['tab1Data'][1]['value1'] = $db[$i]['bak'] / 10000;
    //             $res['tab1Data'][2]['value1'] = $db[$i]['frk'] / 10000;
    //             $res['tab1Data'][3]['value1'] = $db[$i]['brk'] / 10000;
    //         }
    //         if ($db[$i]['meter_id'] === '2') {
    //             $res['tab1Data'][0]['value2'] = $db[$i]['fak'] / 10000;
    //             $res['tab1Data'][1]['value2'] = $db[$i]['bak'] / 10000;
    //             $res['tab1Data'][2]['value2'] = $db[$i]['frk'] / 10000;
    //             $res['tab1Data'][3]['value2'] = $db[$i]['brk'] / 10000;
    //         }
    //         //
    //         if ($db[$i]['meter_id'] === '3') {
    //             $res['tab2Data'][0]['value1'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value1'] = $db[$i]['bak'] / 100;
    //             $res['tab2Data'][2]['value1'] = $db[$i]['frk'] / 100;
    //             $res['tab2Data'][3]['value1'] = $db[$i]['brk'] / 100;
    //             $res['tab2Data'][4]['value1'] = $db[$i]['peak'] / 100;
    //             $res['tab2Data'][5]['value1'] = $db[$i]['valley'] / 100;
    //         }
    //         if ($db[$i]['meter_id'] === '4') {
    //             $res['tab2Data'][0]['value2'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value2'] = $db[$i]['bak'] / 100;
    //             $res['tab2Data'][2]['value2'] = $db[$i]['frk'] / 100;
    //             $res['tab2Data'][3]['value2'] = $db[$i]['brk'] / 100;
    //             $res['tab2Data'][4]['value2'] = $db[$i]['peak'] / 100;
    //             $res['tab2Data'][5]['value2'] = $db[$i]['valley'] / 100;
    //         }
    //         if ($db[$i]['meter_id'] === '5') {
    //             $res['tab2Data'][0]['value3'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value3'] = $db[$i]['bak'] / 100;
    //             $res['tab2Data'][2]['value3'] = $db[$i]['frk'] / 100;
    //             $res['tab2Data'][3]['value3'] = $db[$i]['brk'] / 100;
    //             $res['tab2Data'][4]['value3'] = $db[$i]['peak'] / 100;
    //             $res['tab2Data'][5]['value3'] = $db[$i]['valley'] / 100;
    //         }
    //         //
    //         if ($db[$i]['meter_id'] === '6') {
    //             $res['tab3Data'][0]['value1'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value1'] = $db[$i]['bak'] / 100;
    //             // $res['tab3Data'][2]['value1'] = $db[$i]['frk'] / 100;
    //             // $res['tab3Data'][3]['value1'] = $db[$i]['brk'] / 100;
    //             // $res['tab3Data'][4]['value1'] = $db[$i]['peak'] / 100;
    //             // $res['tab3Data'][5]['value1'] = $db[$i]['valley'] / 100;
    //         }
    //         if ($db[$i]['meter_id'] === '7') {
    //             $res['tab3Data'][0]['value2'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value1'] = $db[$i]['bak'] / 100;
    //             // $res['tab3Data'][2]['value1'] = $db[$i]['frk'] / 100;
    //             // $res['tab3Data'][3]['value1'] = $db[$i]['brk'] / 100;
    //             // $res['tab3Data'][4]['value1'] = $db[$i]['peak'] / 100;
    //             // $res['tab3Data'][5]['value1'] = $db[$i]['valley'] / 100;
    //         }
    //         if ($db[$i]['meter_id'] === '8') {
    //             $res['tab3Data'][0]['value3'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value1'] = $db[$i]['bak'] / 100;
    //             // $res['tab3Data'][2]['value1'] = $db[$i]['frk'] / 100;
    //             // $res['tab3Data'][3]['value1'] = $db[$i]['brk'] / 100;
    //             // $res['tab3Data'][4]['value1'] = $db[$i]['peak'] / 100;
    //             // $res['tab3Data'][5]['value1'] = $db[$i]['valley'] / 100;
    //         }
    //         //
    //         if ($db[$i]['meter_id'] === '9') {
    //             $res['tab4Data'][0]['value1'] = $db[$i]['fak'] / 100;
    //             // $res['tab2Data'][1]['value1'] = $db[$i]['bak'] / 100;
    //             // $res['tab3Data'][2]['value1'] = $db[$i]['frk'] / 100;
    //             // $res['tab3Data'][3]['value1'] = $db[$i]['brk'] / 100;
    //             // $res['tab3Data'][4]['value1'] = $db[$i]['peak'] / 100;
    //             // $res['tab3Data'][5]['value1'] = $db[$i]['valley'] / 100;
    //         }
    //     }

    //     return $res;
    // }

    // 2021-11-21
    public function getStatisticChartData()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterGetStatisticChartData')) {
            $res['error'] = $this->validator->getErrors();

            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        // 取查询参数
        $station_id = $param['station_id'];
        $date       = $param['date'];
        $log_time   = '23:59:00';

        // 查找最近一条23:59记录的日期
        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
        ];
        $utils             = service('mixUtils');
        $query['start_at'] = $utils->getFirstDayOfYear($date);
        $query['end_at']   = $utils->getLastDayOfYear($date);

        $model      = new RecordModel();
        $columnName = ['log_date'];
        $db         = $model->getByStationDateRangeTime($columnName, $query);
        if (!isset($db['log_date'])) {
            $res['code'] = EXIT_SUCCESS;
            return $this->respond($res);
        }

        $log_date = $db['log_date'];

        // 查询计划、成交
        $query = [
            'station_id' => $station_id,
            'year'       => date('Y', strtotime($log_date)),
        ];
        $planAndDeal = $this->getPlanAndDealForStatisticChart($query);

        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];

        // 统计图
        //
        $thirtyDays      = $this->get30Days($query);
        $generator30Days = [];
        $line30Days      = [];
        $cnt             = count($thirtyDays);
        for ($i = 0; $i < $cnt; $i++) {
            $generator30Days[] = [
                'date' => $thirtyDays[$i]['date'],
                'real' => $thirtyDays[$i]['generator'],
            ];
            $line30Days[] = [
                'date' => $thirtyDays[$i]['date'],
                'real' => $thirtyDays[$i]['line'],
            ];
        }

        //
        $months            = $this->get12Months($query);
        $generator12Months = [];
        $line12Months      = [];
        $cnt               = count($months);
        for ($i = 0; $i < $cnt; $i++) {
            $generator12Months[] = [
                'month' => $months[$i]['month'],
                'plan'  => $planAndDeal['month'][$i]['plan'],
                'real'  => $months[$i]['generator'],
            ];
            $line12Months[] = [
                'month' => $months[$i]['month'],
                'deal'  => $planAndDeal['month'][$i]['deal'],
                'real'  => $months[$i]['line'],
            ];
        }

        //
        $quarters           = $this->get4Quarters($query);
        $generator4Quarters = [];
        $line4Quarters      = [];
        $cnt                = count($quarters);
        for ($i = 0; $i < $cnt; $i++) {
            $generator4Quarters[] = [
                'quarter' => $quarters[$i]['quarter'],
                'plan'    => $planAndDeal['quarter'][$i]['plan'],
                'real'    => $quarters[$i]['generator'],
            ];
            $line4Quarters[] = [
                'quarter' => $quarters[$i]['quarter'],
                'deal'    => $planAndDeal['quarter'][$i]['deal'],
                'real'    => $quarters[$i]['line'],
            ];
        }

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = [
            'date'               => $log_date . ' ' . $log_time,
            //
            'generator30Days'    => $generator30Days,
            'generator12Months'  => $generator12Months,
            'generator4Quarters' => $generator4Quarters,
            //
            'line30Days'         => $line30Days,
            'line12Months'       => $line12Months,
            'line4Quarters'      => $line4Quarters,
        ];

        return $this->respond($res);
    }

    // 2021-11-21
    protected function getPlanAndDealForStatisticChart($query)
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

        //
        $model = new PlanKWhModel();
        $db    = $model->getByStationYear($query);

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
        }

        $quarter[0]['plan'] = ($db[0]['planning'] + $db[1]['planning'] + $db[2]['planning']) / $this->unitRatio;
        $quarter[1]['plan'] = ($db[3]['planning'] + $db[4]['planning'] + $db[5]['planning']) / $this->unitRatio;
        $quarter[2]['plan'] = ($db[6]['planning'] + $db[7]['planning'] + $db[8]['planning']) / $this->unitRatio;
        $quarter[3]['plan'] = ($db[9]['planning'] + $db[10]['planning'] + $db[11]['planning']) / $this->unitRatio;

        $quarter[0]['deal'] = ($db[0]['deal'] + $db[1]['deal'] + $db[2]['deal']) / $this->unitRatio;
        $quarter[1]['deal'] = ($db[3]['deal'] + $db[4]['deal'] + $db[5]['deal']) / $this->unitRatio;
        $quarter[2]['deal'] = ($db[6]['deal'] + $db[7]['deal'] + $db[8]['deal']) / $this->unitRatio;
        $quarter[3]['deal'] = ($db[9]['deal'] + $db[10]['deal'] + $db[11]['deal']) / $this->unitRatio;

        return [
            'month'   => $month,
            'quarter' => $quarter,
        ];
    }

    // 2021-11-21
    protected function get30Days($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils    = service('mixUtils');
        $length   = 30;
        $start_at = $utils->getPlusOffsetDay($log_date, -$length);
        $end_at   = $log_date;

        // 初值
        $res      = [];
        $dates[0] = $start_at;
        for ($i = 0; $i < $length; $i++) {
            $date    = $utils->getPlusOffsetDay($log_date, -($length - 1 - $i));
            $res[$i] = [
                'date'      => $date,
                'generator' => 0,
                'line'      => 0,
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
        $columnName = ['log_date', 'meter_id', 'fak'];

        $model = new RecordModel();
        $db    = $model->getByStationDateRangeTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // 表读数求和
        $generator = [];
        $line      = [];
        $cnt       = count($dates);
        $cnt2      = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $line[$date] = ['fak' => $item['fak']];
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
            $generator[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($res);
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($generator[$b]['fak'] > $generator[$a]['fak']) && ($generator[$a]['fak'] > 0)) {
                $res[$i]['generator'] = ($generator[$b]['fak'] - $generator[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($line[$b]['fak'] > $line[$a]['fak']) && ($line[$a]['fak'] > 0)) {
                $res[$i]['line'] = ($line[$b]['fak'] - $line[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;
    }

    // 2021-11-21
    protected function get12Months($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils  = service('mixUtils');
        $length = 12;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'month'     => ($i + 1) . '月',
                'generator' => 0,
                'line'      => 0,
            ];
        }

        // 每月的最后一天的日期
        $month = date('n', strtotime($log_date));
        $dates = [];
        for ($i = 0; $i < $month; $i++) {
            $dates[] = $utils->getLastDayOfMonth($log_date, (-$month + $i));
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
        $columnName = ['log_date', 'meter_id', 'fak'];

        $model = new RecordModel();
        $db    = $model->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // sum
        // $db2  = [];
        // $cnt  = count($dates);
        // $cnt2 = count($db);
        // for ($i = 0; $i < $cnt; $i++) {
        //     $temp = 0;
        //     for ($j = 0; $j < $cnt2; $j++) {
        //         if ($dates[$i] === $db[$j]['log_date']) {
        //             $temp = $temp + $db[$j]['fak'];
        //         }
        //     }
        //     $db2[] = ['date' => $dates[$i], 'real' => $temp];
        // }

        // // delta
        // $cnt = count($db2);
        // for ($i = 0; $i < ($cnt - 1); $i++) {
        //     if (($db2[$i + 1]['real'] > $db2[$i]['real']) && ($db2[$i]['real'] > 0)) {
        //         $res[$i]['real'] = $db2[$i + 1]['real'] - $db2[$i]['real'];
        //     }
        // }

        // return $res;

        // 表读数求和
        $generator = [];
        $line      = [];
        $cnt       = count($dates);
        $cnt2      = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $line[$date] = ['fak' => $item['fak']];
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
            $generator[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($dates) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($generator[$b]['fak'] > $generator[$a]['fak']) && ($generator[$a]['fak'] > 0)) {
                $res[$i]['generator'] = ($generator[$b]['fak'] - $generator[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($line[$b]['fak'] > $line[$a]['fak']) && ($line[$a]['fak'] > 0)) {
                $res[$i]['line'] = ($line[$b]['fak'] - $line[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;

    }

    // 2021-11-21
    protected function get4Quarters($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils  = service('mixUtils');
        $length = 4;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'quarter'   => ($i + 1) . '季度',
                'generator' => 0,
                'line'      => 0,
            ];
        }

        // 每季度的最后一天的日期
        $quarter = ceil((date('n', strtotime($log_date))) / 3);
        $dates   = [];
        for ($i = 0; $i < $quarter; $i++) {
            $dates[] = $utils->getLastDayOfQuarter($log_date, (-$quarter + $i));
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
        $columnName = ['log_date', 'meter_id', 'fak'];

        $model = new RecordModel();
        $db    = $model->getByStationDatesTimeMeters($columnName, $query2);
        if (empty($db)) {
            return $res;
        }

        // // sum
        // $db2  = [];
        // $cnt  = count($dates);
        // $cnt2 = count($db);
        // for ($i = 0; $i < $cnt; $i++) {
        //     $temp = 0;
        //     for ($j = 0; $j < $cnt2; $j++) {
        //         if ($dates[$i] === $db[$j]['log_date']) {
        //             $temp = $temp + $db[$j]['fak'];
        //         }
        //     }
        //     $db2[] = ['date' => $dates[$i], 'real' => $temp];
        // }

        // // delta
        // $cnt = count($db2);
        // for ($i = 0; $i < ($cnt - 1); $i++) {
        //     if (($db2[$i + 1]['real'] > $db2[$i]['real']) && ($db2[$i]['real'] > 0)) {
        //         $res[$i]['real'] = $db2[$i + 1]['real'] - $db2[$i]['real'];
        //     }
        // }

        // return $res;

        // 表读数求和
        $generator = [];
        $line      = [];
        $cnt       = count($dates);
        $cnt2      = count($db);
        for ($i = 0; $i < $cnt; $i++) {
            $date = $dates[$i];
            $fak  = 0;
            for ($j = 0; $j < $cnt2; $j++) {
                $item = $db[$j];
                if ($item['meter_id'] == $this->mainMeterId) {
                    if ($item['log_date'] === $date) {
                        $line[$date] = ['fak' => $item['fak']];
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
            $generator[$date] = ['fak' => $fak];
        }

        // delta， 单位：DB kwh -> Web 万kWh
        $cnt = count($dates) - 1;
        for ($i = 0; $i < $cnt; $i++) {
            $a = $dates[$i];
            $b = $dates[$i + 1];
            if (($generator[$b]['fak'] > $generator[$a]['fak']) && ($generator[$a]['fak'] > 0)) {
                $res[$i]['generator'] = ($generator[$b]['fak'] - $generator[$a]['fak']) * $this->genMeterRatio / $this->unitRatio;
            }
            if (($line[$b]['fak'] > $line[$a]['fak']) && ($line[$a]['fak'] > 0)) {
                $res[$i]['line'] = ($line[$b]['fak'] - $line[$a]['fak']) * $this->mainMeterRatio / $this->unitRatio;
            }
        }

        return $res;
    }
}
