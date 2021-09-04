<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-04 21:37:54
 */

namespace App\Controllers;

use App\Models\Meter\MeterLogModel;
use App\Models\Meter\PlanningKWhModel;
use CodeIgniter\API\ResponseTrait;

class Meters extends BaseController
{
    use ResponseTrait;

    // 电表的个数
    protected $meterNumber;

    // 主表的编号，全厂电量统计取线路主表记录
    protected $mainMeterId;

    // 发电机电表编号
    protected $firstGenMeterId;
    protected $lastGenMeterId;

    // 电表读数倍率
    protected $mainMeterRate;
    protected $genMeterRate;
    protected $transformMeterRate;

    protected $meterLogModel;
    protected $planningKWhModel;

    public function __construct()
    {
        $this->meterNumber     = 9;
        $this->mainMeterId     = 1;
        $this->firstGenMeterId = 3;
        $this->lastGenMeterId  = 5;

        // 1320000 kWh，4位小数，入库前乘以10000，得到整数
        $this->mainMeterRate = 132;
        // 500000 kWh，2位小数，入库前乘以100，得到整数
        $this->genMeterRate = 5000;
        // 20000 kWh，2位小数，入库前乘以100，得到整数
        $this->transformMeterRate = 200;

        $this->meterLogModel    = new MeterLogModel();
        $this->planningKWhModel = new PlanningKWhModel();
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
            if (count($client['meter']) !== $this->meterNumber) {
                $valid = false;
            } else {
                for ($i = 0; $i < $this->meterNumber; $i++) {
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

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '没有权限';
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

        // 是否已存在该日期/时间的记录
        $hasLogs = $this->meterLogModel->hasSameLogByStationAndDateTime($station_id, $log_date, $log_time);
        if ($hasLogs) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '输入的日期已存在记录';
            return $this->respond($res);
        }

        // 查找前一天同一时间点记录
        $utils   = service('mixUtils');
        $prevDay = $utils->getPlusOffsetDay($log_date, -1);
        $query   = [
            'station_id' => $station_id,
            'log_date'   => $prevDay,
            'log_time'   => $log_time,
        ];
        $lastLogs = $this->meterLogModel->getLogByStationDateTime($query);
        if (empty($lastLogs)) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '没有前一天的记录，不允许新的记录';
            return $this->respond($res);
        }

        // 无前一天数据
        // if (count($lastLogs) === 0) {
        //     $result = $this->meterLogModel->batchInsert($meters, $others);
        //     if ($result) {
        //         $res['code'] = EXIT_SUCCESS;
        //         $res['msg']  = '添加新记录';
        //     } else {
        //         $res['code'] = EXIT_ERROR;
        //         $res['msg']  = '添加失败，稍后再试';
        //     }
        //     return $this->respond($res);
        // }

        // 比对电表个数
        if (count($lastLogs) != $this->meterNumber) {
            $res['error'] = 'last log num != ' . $this->meterNumber;
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 比较日期/时间
        // if (strtotime($lastLogs[0]['log_date']) > strtotime($log_date)) {
        //     $res['error'] = 'data early';
        //     $res['code']  = EXIT_ERROR;
        //     $res['msg']   = '输入的日期有误';
        //     return $this->respond($res);
        // }

        // 非首次记录，计算日增量
        for ($i = 0; $i < count($meters); $i++) {
            // fak 正向有功
            if ($meters[$i]['fak'] > $lastLogs[$i]['fak']) {
                $meters[$i]['fak_delta'] = $meters[$i]['fak'] - $lastLogs[$i]['fak'];
            } else {
                $meters[$i]['bak'] = $lastLogs[$i]['bak'];
            }
            // bak 反向有功
            if ($meters[$i]['bak'] > $lastLogs[$i]['bak']) {
                $meters[$i]['bak_delta'] = $meters[$i]['bak'] - $lastLogs[$i]['bak'];
            } else {
                $meters[$i]['bak'] = $lastLogs[$i]['bak'];
            }
            // frk 正向无功
            if ($meters[$i]['frk'] > $lastLogs[$i]['frk']) {
                $meters[$i]['frk_delta'] = $meters[$i]['frk'] - $lastLogs[$i]['frk'];
            } else {
                $meters[$i]['frk'] = $lastLogs[$i]['frk'];
            }
            // brk 反向无功
            if ($meters[$i]['brk'] > $lastLogs[$i]['brk']) {
                $meters[$i]['brk_delta'] = $meters[$i]['brk'] - $lastLogs[$i]['brk'];
            } else {
                $meters[$i]['brk'] = $lastLogs[$i]['brk'];
            }
            // peak 高峰
            if ($meters[$i]['peak'] > $lastLogs[$i]['peak']) {
                $meters[$i]['peak_delta'] = $meters[$i]['peak'] - $lastLogs[$i]['peak'];
            } else {
                $meters[$i]['peak'] = $lastLogs[$i]['peak'];
            }
            // valley 低谷
            if ($meters[$i]['valley'] > $lastLogs[$i]['valley']) {
                $meters[$i]['valley_delta'] = $meters[$i]['valley'] - $lastLogs[$i]['valley'];
            } else {
                $meters[$i]['valley'] = $lastLogs[$i]['valley'];
            }

        }

        //
        $result = $this->meterLogModel->batchInsert($meters, $others);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '添加一条新记录';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '添加失败，稍后再试';
        }

        return $this->respond($res);
    }

    public function getMeterLogs()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterLogsGet')) {
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
            $res['msg']   = '没有权限';
            return $this->respond($res);
        }

        $query['station_id'] = $param['station_id'];
        $query['start']      = $param['date'];
        $query['end']        = $param['date'];
        $query['limit']      = $param['limit'];
        $query['offset']     = $param['offset'];

        $type = $param['type'];
        if ($type === 'month') {
            $utils          = service('mixUtils');
            $query['start'] = $utils->getFirstDayOfMonth($param['date']);
            $query['end']   = $utils->getLastDayOfMonth($param['date']);
        }

        $columnName = ['id', 'station_id', 'log_date', 'log_time', 'creator'];
        $result     = $this->meterLogModel->getLogsForShowList($columnName, $query);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['data'] = ['total' => $result['total'], 'data' => $result['result']];
        } else {
            $res['code'] = EXIT_ERROR;
        }

        return $this->respond($res);
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

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($param['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '没有权限';
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

        // 计算发电量
        $genEnergy = $this->getGenEnergyForDailyReport($param);

        // 计算上网电量
        $onGridEnergy = $this->getOnGridEnergyForDailyReport($param);

        // 计算完成率
        $rates = $this->getCompletionRate($log_date, $planning, $genEnergy, $onGridEnergy);

        // 写简报
        $stationName = $this->session->get('belongToDeptName');
        $report      = $this->editDailyReports($stationName, $log_date, $rates, $genEnergy, $onGridEnergy);

        $res['planning']     = $planning;
        $res['genEnergy']    = $genEnergy;
        $res['onGridEnergy'] = $onGridEnergy;
        $res['rates']        = $rates;
        $res['report']       = $report;
        $res['code']         = EXIT_ERROR;
        $res['msg']          = '测试';
        return $this->respond($res);

    }

    public function getBasicStatistic()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MeterBasicStatisticGet')) {
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
            $res['msg']   = '没有权限';
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
        // if (!isset($db['log_date'])) {
        //     $res['code'] = EXIT_SUCCESS;
        // }
        $log_date = $db['log_date'];

        // 查询年计划、月成交量
        $query = [
            'station_id' => $station_id,
            'year'       => date('Y', strtotime($log_date)),
        ];
        $planning = $this->getPlanningForDailyReport($query);

        // 计算发电量
        $query = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
        ];
        $genEnergy = $this->getGenEnergyForDailyReport($query);

        // 计算上网电量
        $onGridEnergy = $this->getOnGridEnergyForDailyReport($query);

        // 计算完成率
        $rates = $this->getCompletionRate($log_date, $planning, $genEnergy, $onGridEnergy);

        // 统计简表
        $thirtyDaysGenEnergy = $this->getThirtyDaysDailyEnergyForBasicStatistic($query);

        $statisticResult = $this->editBasicStatistic($rates, $genEnergy, $onGridEnergy, $thirtyDaysGenEnergy);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = [
            'date'           => $log_date . ' ' . $log_time,
            'statisticList'  => $statisticResult['statisticList'],
            'thirtyDaysData' => $statisticResult['thirtyDaysData'],
        ];

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

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            $res['error'] = 'invalid station_id';
            return $this->respond($res);
        }

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
        $query = [
            'station_id' => $station_id,
            'log_time'   => $log_time,
        ];
        $columnName = ['log_date'];
        $db         = $this->meterLogModel->getLastDateByStationTime($columnName, $query);
        if ($db['log_date'] !== $log_date) {
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '不是时间最近的一条记录';
            $res['error'] = 'not last record';
            return $this->respond($res);
        }

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

    public function getPlanningKWh()
    {
        $param = $this->request->getGet();

        // 检查请求数据
        if (!$this->validate('MetersPlanningKWhGet')) {
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
            $res['msg']   = '没有权限';
            return $this->respond($res);
        }

        $date = $param['date'];

        $query['station_id'] = $param['station_id'];
        $query['year']       = substr($date, 0, 4);

        $planning = $this->planningKWhModel->getByStationYear($query);

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['data' => $planning];

        return $this->respond($res);
    }

    public function updatePlanningKWhRecord()
    {
        // 检查请求数据
        if (!$this->validate('MeterPlanningKWhRecordUpdate')) {
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

        // 与session比对
        $stationID = $this->session->get('belongToDeptId');
        if ($client['station_id'] != $stationID) {
            $res['error'] = 'invalid station_id';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '没有权限';
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
        $db = $this->planningKWhModel->getByStationYearMonth($query);
        if ($id !== $db[0]['id']) {
            $res['error'] = 'conflict with DB';
            $res['code']  = EXIT_ERROR;
            $res['msg']   = '请求数据无效';
            return $this->respond($res);
        }

        // 修改
        $result = $this->planningKWhModel->doSave($data);

        if ($result) {
            $res['code'] = EXIT_SUCCESS;
            $res['msg']  = '修改一条记录';
        } else {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '修改失败，稍后再试';
        }

        return $this->respond($res);
    }

    // 内部方法
    protected function getPlanningForDailyReport($query)
    {

        $planning = $this->planningKWhModel->getByStationYear($query);

        $totalMonthOfYear = 12;
        if (empty($planning) || count($planning) !== $totalMonthOfYear) {
            return false;
        }

        $planning_total = 0;
        for ($i = 0; $i < count($planning); $i++) {
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

    protected function getGenEnergyForDailyReport($param)
    {
        $station_id = $param['station_id'];
        $log_date   = $param['log_date'];
        $log_time   = $param['log_time'];

        // 初值0
        $res = [
            'daily'        => 0,
            'weekly'       => 0,
            'monthly'      => 0,
            'quarterly'    => 0,
            'yearly'       => 0,
            'daily_peak'   => 0,
            'daily_valley' => 0,
        ];
        $utils = service('mixUtils');

        // 查询给定日期：日电量，表读数，峰谷电量
        $columnName = ['fak', 'fak_delta', 'peak_delta', 'valley_delta'];
        $query      = [
            'station_id'     => $station_id,
            'log_date'       => $log_date,
            'log_time'       => $log_time,
            'first_meter_id' => $this->firstGenMeterId,
            'last_meter_id'  => $this->lastGenMeterId,
        ];
        $currentDayLog = $this->meterLogModel->sumByStationDateTimeMeterIds($columnName, $query);

        if (empty($currentDayLog)) {
            return $res;
        }
        // 日发电量
        $res['daily'] = $currentDayLog['sum_fak_delta'];
        // 日峰谷电量
        $res['daily_peak']   = $currentDayLog['sum_peak_delta'];
        $res['daily_valley'] = $currentDayLog['sum_valley_delta'];

        // 表读数
        $currentDayFak = $currentDayLog['sum_fak'];

        // 查询上一周：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getDayOfPreviousWeek($log_date);
        $previousWeek      = $this->meterLogModel->sumByStationDateTimeMeterIds($columnName, $query);

        if (!empty($previousWeek) && $previousWeek['sum_fak'] !== null) {
            if ($previousWeek['sum_fak'] < $currentDayFak) {
                $res['weekly'] = $currentDayFak - $previousWeek['sum_fak'];
            }
        }

        // 查询前一个月：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfMonth($log_date, -1);
        $previousMonth     = $this->meterLogModel->sumByStationDateTimeMeterIds($columnName, $query);

        if (!empty($previousMonth) && $previousMonth['sum_fak'] !== null) {
            if ($previousMonth['sum_fak'] < $currentDayFak) {
                $res['monthly'] = $currentDayFak - $previousMonth['sum_fak'];
            }
        }

        // 查询前一个季度：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfQuarter($log_date, -1);
        $previousQuarter   = $this->meterLogModel->sumByStationDateTimeMeterIds($columnName, $query);

        if (!empty($previousQuarter) && $previousQuarter['sum_fak'] !== null) {
            if ($previousQuarter['sum_fak'] < $currentDayFak) {
                $res['quarterly'] = $currentDayFak - $previousQuarter['sum_fak'];
            }
        }

        // 查询前一年：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfYear($log_date, -1);
        $previousYear      = $this->meterLogModel->sumByStationDateTimeMeterIds($columnName, $query);

        if (!empty($previousYear) && $previousYear['sum_fak'] !== null) {
            if ($previousYear['sum_fak'] < $currentDayFak) {
                $res['yearly'] = $currentDayFak - $previousYear['sum_fak'];
            }
        }

        // 计算结束
        return $res;
    }

    protected function getOnGridEnergyForDailyReport($param)
    {
        $station_id = $param['station_id'];
        $log_date   = $param['log_date'];
        $log_time   = $param['log_time'];

        // 初值0
        $res = [
            'daily'     => 0,
            'weekly'    => 0,
            'monthly'   => 0,
            'quarterly' => 0,
            'yearly'    => 0,
        ];
        $utils = service('mixUtils');

        // 查询给定日期：日电量，表读数
        $columnName = ['fak', 'fak_delta'];
        $query      = [
            'station_id' => $station_id,
            'log_date'   => $log_date,
            'log_time'   => $log_time,
            'meter_id'   => $this->mainMeterId,
        ];
        $currentDayLog = $this->meterLogModel->getByStationDateTimeMeterId($columnName, $query);

        if (empty($currentDayLog)) {
            return $res;
        }
        // 日发电量
        $res['daily'] = $currentDayLog['fak_delta'];
        // 表读数
        $currentDayFak = $currentDayLog['fak'];

        // 查询上一周：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getDayOfPreviousWeek($log_date);
        $previousWeek      = $this->meterLogModel->getByStationDateTimeMeterId($columnName, $query);

        if (!empty($previousWeek) && $previousWeek['fak'] !== null) {
            if ($previousWeek['fak'] < $currentDayFak) {
                $res['weekly'] = $currentDayFak - $previousWeek['fak'];
            }
        }

        // 查询前一个月：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfMonth($log_date, -1);
        $previousMonth     = $this->meterLogModel->getByStationDateTimeMeterId($columnName, $query);

        if (!empty($previousMonth) && $previousMonth['fak'] !== null) {
            if ($previousMonth['fak'] < $currentDayFak) {
                $res['monthly'] = $currentDayFak - $previousMonth['fak'];
            }
        }

        // 查询前一个季度：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfQuarter($log_date, -1);
        $previousQuarter   = $this->meterLogModel->getByStationDateTimeMeterId($columnName, $query);

        if (!empty($previousQuarter) && $previousQuarter['fak'] !== null) {
            if ($previousQuarter['fak'] < $currentDayFak) {
                $res['quarterly'] = $currentDayFak - $previousQuarter['fak'];
            }
        }

        // 查询前一年：表读数
        $columnName        = ['fak'];
        $query['log_date'] = $utils->getLastDayOfYear($log_date, -1);
        $previousYear      = $this->meterLogModel->getByStationDateTimeMeterId($columnName, $query);

        if (!empty($previousYear) && $previousYear['fak'] !== null) {
            if ($previousYear['fak'] < $currentDayFak) {
                $res['yearly'] = $currentDayFak - $previousYear['fak'];
            }
        }

        // 计算结束
        return $res;
    }

    protected function getThirtyDaysDailyEnergyForBasicStatistic($query)
    {
        $station_id = $query['station_id'];
        $log_date   = $query['log_date'];
        $log_time   = $query['log_time'];

        $utils    = service('mixUtils');
        $length   = 30;
        $start_at = $utils->getPlusOffsetDay($log_date, -($length - 1));
        $end_at   = $log_date;

        // 初值0
        $res = [];
        for ($i = 0; $i < $length; $i++) {
            $res[$i] = [
                'date' => $utils->getPlusOffsetDay($log_date, -($length - 1 - $i)),
                'real' => 0,
            ];
        }

        $query2 = [
            'station_id'     => $station_id,
            'start_at'       => $start_at,
            'end_at'         => $end_at,
            'log_time'       => $log_time,
            'first_meter_id' => $this->firstGenMeterId,
            'last_meter_id'  => $this->lastGenMeterId,
        ];
        $columnName = ['log_date, fak_delta'];
        $db         = $this->meterLogModel->getByStationDateTimeMeterIds($columnName, $query2);

        $temp = [];
        $his  = [];
        for ($i = 0; $i < count($db); $i++) {
            $dateKey = $db[$i]['date'];
            $value   = 0;
            if (isset($his[$dateKey])) {
                continue;
            } else {
                for ($j = 0; $j < count($db); $j++) {
                    if ($dateKey === $db[$j]['date']) {
                        $value = $value + $db[$j]['real'];
                    }
                }
                $temp[]        = ['date' => $dateKey, 'real' => $value];
                $his[$dateKey] = $dateKey;
            }
        }

        for ($i = 0; $i < count($res); $i++) {
            for ($j = 0; $j < count($temp); $j++) {
                if ($res[$i]['date'] === $temp[$j]['date']) {
                    $res[$i]['real'] = $temp[$j]['real'];
                }
            }
        }

        return $res;
    }

    protected function getCompletionRate($log_date, $planning, $genEnergy, $onGridEnergy)
    {
        if (empty($planning) || empty($genEnergy) || empty($onGridEnergy) || empty($log_date)) {
            return false;
        }

        $month   = date('n', strtotime($log_date));
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

        // 数据库中，电表读数没有乘以表倍率，单位kWh；计划电量用户数据格式为4为小数 万kWh，入库前乘以10000，得到整数，单位kWh

        // 月计划完成率：机组 / 月计划发电量
        if (!empty($planning['month'][$month - 1]['planning'])) {
            $rate         = round($genEnergy['monthly'] * $this->genMeterRate / $planning['month'][$month - 1]['planning'], $precision);
            $res['month'] = ($rate * 100) . '%';
        }

        // 月成交完成率：线路上网 / 月成交电量
        if (!empty($planning['month'][$month - 1]['deal'])) {
            $rate              = round($onGridEnergy['monthly'] * $this->mainMeterRate / $planning['month'][$month - 1]['deal'], $precision);
            $res['month_deal'] = ($rate * 100) . '%';
        }

        // 季度计划完成率：机组 / 季度计划发电量
        if (!empty($planning['quarter'][$quarter - 1])) {
            $rate           = round($genEnergy['quarterly'] * $this->genMeterRate / $planning['quarter'][$quarter - 1], $precision);
            $res['quarter'] = ($rate * 100) . '%';
        }

        // 年度计划完成率：机组 / 年度计划发电量
        if (!empty($planning['planning_total'])) {
            $rate        = round($genEnergy['yearly'] * $this->genMeterRate / $planning['planning_total'], $precision);
            $res['year'] = ($rate * 100) . '%';
        }

        return $res;

    }

    protected function editDailyReports($stationName, $log_date, $rates, $genEnergy, $onGridEnergy)
    {
        if (empty($stationName) || empty($log_date) || empty($rates) || empty($genEnergy) || empty($onGridEnergy)) {
            return false;
        }

        $res = [
            'daily1' => '',
            'weekly' => '',
            'daily2' => '',
        ];

        // 日期
        $timestamp = strtotime($log_date);
        $year      = date('Y', $timestamp) . '年';
        $month     = date('m', $timestamp) . '月';
        $day       = date('d', $timestamp) . '日';

        $arr     = ['一', '二', '三', '四'];
        $quarter = $arr[ceil(date('n', $timestamp) / 3) - 1];

        $precision = 4;

        // 电表倍率，单位换算
        $dailyGenEnergy     = round($genEnergy['daily'] * $this->genMeterRate / 10000, $precision);
        $monthlyGenEnergy   = round($genEnergy['monthly'] * $this->genMeterRate / 10000, $precision);
        $quarterlyGenEnergy = round($genEnergy['quarterly'] * $this->genMeterRate / 10000, $precision);
        $yearlyGenEnergy    = round($genEnergy['yearly'] * $this->genMeterRate / 10000, $precision);

        $res['daily1'] = $stationName . ' '
            . $year . $month . $day . '，发电量为' . $dailyGenEnergy . '万千瓦时。'
            . '截止' . $month . $day . '24时，电厂本月完成发电量' . $monthlyGenEnergy . '万千瓦时，完成月度发电计划的' . $rates['month'] . '，'
            . $quarter . '季度累计完成发电量' . $quarterlyGenEnergy . '万千瓦时，完成季度发电计划的' . $rates['quarter'] . '，'
            . '本年累计完成发电量' . $yearlyGenEnergy . '万千瓦时，完成年度发电计划的' . $rates['year'] . '。'
            . '本日弃水电量为0万kWh，本月弃水电量累计0万kWh。';

        $dailyOnGridEnergy   = round($onGridEnergy['daily'] * $this->mainMeterRate / 10000, $precision);
        $monthlyOnGridEnergy = round($onGridEnergy['monthly'] * $this->mainMeterRate / 10000, $precision);
        $yearlyOnGridEnergy  = round($onGridEnergy['yearly'] * $this->mainMeterRate / 10000, $precision);

        $res['daily2'] = $stationName . '：'
            . $year . $month . $day . '发电机组为#1、#2、#3机组，出力在0MW到161MW之间，'
            . '机组日实际发电量为' . $dailyGenEnergy . '万kWh，'
            . '日上网电量为' . $dailyOnGridEnergy . '万kWh。'
            . '截止' . $month . $day . '24时，电厂本月完成上网电量' . $monthlyOnGridEnergy . '万kWh，完成月度成交电量的' . $rates['month_deal'] . '，'
            . '本年完成上网电量' . $yearlyOnGridEnergy . '万kWh。'
            . '本日弃水电量为0万kWh，本月弃水电量累计0万kWh。';

        $utils        = service('mixUtils');
        $previousWeek = $utils->getDayOfPreviousWeek($log_date);
        $temp         = $utils->getPlusOffsetDay($previousWeek);
        $timestamp    = strtotime($temp);
        $year1        = date('Y', $timestamp) . '年';
        $month1       = date('m', $timestamp) . '月';
        $day1         = date('d', $timestamp) . '日';

        $temp      = $utils->getPlusOffsetDay($log_date, 1);
        $timestamp = strtotime($temp);
        $year2     = date('Y', $timestamp) . '年';
        $month2    = date('m', $timestamp) . '月';
        $day2      = date('d', $timestamp) . '日';

        $weeklyGenEnergy    = round($genEnergy['weekly'] * $this->genMeterRate / 10000, $precision);
        $weeklyOnGirdEnergy = round($onGridEnergy['weekly'] * $this->mainMeterRate / 10000, $precision);

        $res['weekly'] = $stationName . '本周(' . $year1 . $month1 . $day1 . '00点至' . $year2 . $month2 . $day2 . '00点)'
            . '发电量为' . $weeklyGenEnergy . '万千瓦时，'
            . '上网电量为' . $weeklyOnGirdEnergy . '万千瓦时；'
            . '截止' . $month2 . $day2 . '00点，本月累计发电量为' . $monthlyGenEnergy . '万千瓦时，完成月度发电计划的' . $rates['month'] . '，'
            . '本年度发电量为' . $yearlyGenEnergy . '万千瓦时，完成年度发电计划的' . $rates['year'] . '。';

        return $res;
    }

    protected function editBasicStatistic($rates, $genEnergy, $onGridEnergy, $thirtyDaysGenEnergy)
    {
        if (empty($rates) || empty($genEnergy) || empty($onGridEnergy) || empty($thirtyDaysGenEnergy)) {
            return false;
        }

        $precision = 4;

        // 电表倍率，单位换算
        $dailyGenEnergy       = round($genEnergy['daily'] * $this->genMeterRate / 10000, $precision);
        $weeklyGenEnergy      = round($genEnergy['weekly'] * $this->genMeterRate / 10000, $precision);
        $monthlyGenEnergy     = round($genEnergy['monthly'] * $this->genMeterRate / 10000, $precision);
        $quarterlyGenEnergy   = round($genEnergy['quarterly'] * $this->genMeterRate / 10000, $precision);
        $yearlyGenEnergy      = round($genEnergy['yearly'] * $this->genMeterRate / 10000, $precision);
        $dailyPeakGenEnergy   = round($genEnergy['daily_peak'] * $this->genMeterRate / 10000, $precision);
        $dailyValleyGenEnergy = round($genEnergy['daily_valley'] * $this->genMeterRate / 10000, $precision);

        // 上网，电表倍率，单位换算
        $dailyOnGridEnergy     = round($onGridEnergy['daily'] * $this->mainMeterRate / 10000, $precision);
        $weeklyOnGridEnergy    = round($onGridEnergy['weekly'] * $this->mainMeterRate / 10000, $precision);
        $monthlyOnGridEnergy   = round($onGridEnergy['monthly'] * $this->mainMeterRate / 10000, $precision);
        $quarterlyOnGridEnergy = round($onGridEnergy['quarterly'] * $this->mainMeterRate / 10000, $precision);
        $yearlyOnGridEnergy    = round($onGridEnergy['yearly'] * $this->mainMeterRate / 10000, $precision);

        $statisticList = [
            0 => [
                'id'        => '1',
                'rowHeader' => '今日',
                'onGrid'    => $dailyOnGridEnergy,
                'genEnergy' => $dailyGenEnergy,
                'rate'      => '/',
            ],
            1 => [
                'id'        => '2',
                'rowHeader' => '七日',
                'onGrid'    => $weeklyOnGridEnergy,
                'genEnergy' => $weeklyGenEnergy,
                'rate'      => '/',
            ],
            2 => [
                'id'        => '3',
                'rowHeader' => '本月',
                'onGrid'    => $monthlyOnGridEnergy,
                'genEnergy' => $monthlyGenEnergy,
                'rate'      => $rates['month'],
            ],
            3 => [
                'id'        => '4',
                'rowHeader' => '本季度',
                'onGrid'    => $quarterlyOnGridEnergy,
                'genEnergy' => $quarterlyGenEnergy,
                'rate'      => $rates['quarter'],
            ],
            4 => [
                'id'        => '5',
                'rowHeader' => '全年',
                'onGrid'    => $yearlyOnGridEnergy,
                'genEnergy' => $yearlyGenEnergy,
                'rate'      => $rates['year'],
            ],
            5 => [
                'id'        => '6',
                'rowHeader' => '日高峰',
                'onGrid'    => '/',
                'genEnergy' => $dailyPeakGenEnergy,
                'rate'      => '/',
            ],
            6 => [
                'id'        => '7',
                'rowHeader' => '日低谷',
                'onGrid'    => '/',
                'genEnergy' => $dailyValleyGenEnergy,
                'rate'      => '/',
            ],
        ];

        // 电表倍率，单位换算
        for ($i = 0; $i < count($thirtyDaysGenEnergy); $i++) {
            $thirtyDaysGenEnergy[$i]['real'] = round($thirtyDaysGenEnergy[$i]['real'] * $this->genMeterRate / 10000, $precision);
        }

        return [
            'statisticList'  => $statisticList,
            'thirtyDaysData' => $thirtyDaysGenEnergy,
        ];
    }
}
