<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-30 23:21:03
 */

namespace App\Controllers;

use App\Models\Generator\GenEventLogModel;
use App\Models\Generator\GenEventStatisticModel;
use CodeIgniter\API\ResponseTrait;

class GeneratorEvent extends BaseController
{
    use ResponseTrait;

    protected $eventModel;
    protected $statisticModel;

    public function __construct()
    {
        $this->eventModel     = new GenEventLogModel();
        $this->statisticModel = new GenEventStatisticModel();
    }

    public function getGeneratorEvent()
    {
        $param = $this->request->getGet();

        // 检查输入
        if (empty($param) || !isset($param['station_id']) || !isset($param['limit']) || !isset($param['offset']) || !isset($param['startTimestamp']) || !isset($param['endTimestamp'])) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }
        if (!is_numeric($param['station_id']) || !is_numeric($param['limit']) || !is_numeric($param['offset']) || !is_numeric($param['startTimestamp']) || !is_numeric($param['endTimestamp'])) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = '请求数据无效';
            return $this->respond($res);
        }

        $query['station_id']     = $param['station_id'];
        $query['startTimestamp'] = $param['startTimestamp'];
        $query['endTimestamp']   = $param['endTimestamp'];
        $query['limit']          = $param['limit'];
        $query['offset']         = $param['offset'];

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
            $res['msg']  = '请求数据无效！';
            return $this->respond($res);
        }

        $client = $this->request->getJSON(true);

        // 取出检验后的数据
        $newEvent = [
            'station_id'   => $client['station_id'],
            'generator_id' => $client['generator_id'],
            'event'        => $client['event'],
            'timestamp'    => $client['timestamp'],
            'creator'      => $client['creator'],
        ];

        // 查找最进一条事件
        $lastEvent = $this->eventModel->getLastEventLogByGen([], $newEvent);

        // 检查时间戳，事件的合理性
        $validMsg = $this->validNewEvent($lastEvent, $newEvent);
        if ($validMsg !== true) {
            $res['code'] = EXIT_ERROR;
            $res['msg']  = $validMsg;
            return $this->respond($res);
        }

        // 新事件处理
        $temp = $this->newEventProcess($lastEvent, $newEvent);

        // if ($result) {
        //     $res['code'] = EXIT_SUCCESS;
        //     $res['msg']  = '添加一条新记录！';
        //     $res['data'] = ['id' => $result];
        // } else {
        //     $res['code'] = EXIT_ERROR;
        //     $res['msg']  = '添加失败，稍后再试！';
        // }

        $res['code'] = EXIT_SUCCESS;
        $res['data'] = ['temp' => $temp];

        return $this->respond($res);
    }

    // public function updateRole()
    // {
    //     $client = $this->request->getJSON(true);

    //     $model  = new RoleMode();
    //     $result = $model->save($client);

    //     if ($result) {
    //         $res['code'] = EXIT_SUCCESS;
    //         $res['msg']  = '完成修改！';
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '修改失败，稍后再试！';
    //     }

    //     return $this->respond($res);
    // }

    // public function delRole()
    // {
    //     $client = $this->request->getJSON(true);

    //     $model  = new RoleMode();
    //     $result = $model->delete($client['id']);

    //     if ($result === true) {
    //         $res['code'] = EXIT_SUCCESS;
    //         $res['msg']  = '完成删除！';
    //     } else {
    //         $res['code'] = EXIT_ERROR;
    //         $res['msg']  = '删除失败，稍后再试！';
    //     }

    //     return $this->respond($res);
    // }

    protected function validNewEvent(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($lastEvent) || empty($newEvent)) {
            return false;
        }

        // 检查时间戳，event的合理性
        $now = time();
        // if ($newEvent['timestamp'] < $lastEvent['timestamp'] || $newEvent['timestamp'] > $now) {
        //     return '请检查填写的日期、时间';
        // }
        // 停机，不允许：停机、检修结束
        if (($lastEvent['event'] === '1') && ($newEvent['event'] === '1' || $newEvent['event'] === '4')) {
            return '最后一条记录是停机，请检查填写的事件';
        }
        // 开机，只允许：停机
        if (($lastEvent['event'] === '2') && ($newEvent['event'] !== '1')) {
            return '最后一条记录是开机，请检查填写的事件';
        }
        // 检修开始，只允许：检修结束
        if (($lastEvent['event'] === '3') && ($newEvent['event'] !== '4')) {
            return '最后一条记录是检修开始，请检查填写的事件';
        }
        // 检修结束，不允许：停机、检修结束
        if (($lastEvent['event'] === '4') && ($newEvent['event'] === '1' || $newEvent['event'] === '4')) {
            return '最后一条记录是检修结束，请检查填写的事件';
        }

        return true;
    }

    protected function newEventProcess(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($lastEvent) || empty($newEvent)) {
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

        $year  = date("Y", $newEvent['timestamp']);
        $query = [
            'station_id'   => $newEvent['station_id'],
            'generator_id' => $newEvent['generator_id'],
            'year'         => $year,
        ];

        // 查找统计DB
        $statistic = $this->statisticModel->getStatisticByGen([], $query);
        if (empty($statistic)) {
            // 无统计记录，新建一条
            $statistic = [
                'station_id'     => $newEvent['station_id'],
                'generator_id'   => $newEvent['generator_id'],
                'year'           => $year,
                'status'         => 2, // 发电
                'run_at'         => $newEvent['timestamp'],
                'mnt_at'         => 0,
                'run_num'        => 1,
                'mnt_num'        => 0,
                'run_total_time' => 0,
                'mnt_total_time' => 0,
            ];
        } else {
            // 有统计记录，修改记录
            $statistic['status']  = 2; // 发电
            $statistic['run_at']  = $newEvent['timestamp'];
            $statistic['run_num'] = $statistic['run_num'] + 1;
        }

        // 添加事件
        $eventId = $this->eventModel->newEventLog($newEvent);
        if ($eventId === false) {
            return false;
        }
        // 修改统计
        $res = $this->statisticModel->saveStatistic($statistic);
        if ($res === false) {
            // 回退事件db表的操作
            $this->eventModel->delEventLogById($eventId);
            return false;
        }

        return true;
    }

    protected function newEventGenStop(array $lastEvent = [], array $newEvent = [])
    {
        if (empty($lastEvent) || empty($newEvent)) {
            return false;
        }

        $lastEventYear = date("Y", $lastEvent['timestamp']);
        $newEventYear  = date("Y", $newEvent['timestamp']);

        $query1 = [
            'station_id'   => $newEvent['station_id'],
            'generator_id' => $newEvent['generator_id'],
            'year'         => $lastEventYear,
        ];

        $query2 = [
            'station_id'   => $newEvent['station_id'],
            'generator_id' => $newEvent['generator_id'],
            'year'         => $newEventYear,
        ];

        if ($lastEventYear === $newEventYear) {
            // 计算时长
            $timeLength = ($newEvent['timestamp'] - $lastEvent['timestamp']);

            $newEvent['run_time'] = $timeLength;

            // 查找统计DB
            $lastStatistic = $this->statisticModel->getStatisticByGen([], $query1);

            $lastStatistic['run_total_time'] = $lastStatistic['run_total_time'] + $timeLength;
            $lastStatistic['status']         = 1; // 静止

            // 添加事件
            $eventId = $this->eventModel->newEventLog($newEvent);
            if ($eventId === false) {
                return false;
            }
            // 修改统计
            $res = $this->statisticModel->saveStatistic($lastStatistic);
            if ($res === false) {
                // 回退事件db表的操作
                $this->eventModel->delEventLogById($eventId);
                return false;
            }
        } else {
            // 计算时长
            $lastDay  = mktime(23, 59, 59, 12, 31, $lastEventYear);
            $firstDay = mktime(0, 0, 0, 1, 1, $newEventYear);
            if ($lastDay < $lastEvent['timestamp'] || $firstDay > $newEvent['timestamp']) {
                return false;
            }
            // 事件：X年
            $lastEventCache        = $lastEvent; // 回退时，使用
            $lastEvent['run_time'] = $lastDay - $lastEvent['timestamp'];
            // 事件：X+1年
            $newEvent['run_time'] = $newEvent['timestamp'] - $firstDay;

            // 查找统计DB
            $lastStatistic = $this->statisticModel->getStatisticByGen([], $query1);
            $newStatistic  = $this->statisticModel->getStatisticByGen([], $query2);

            // 统计：X 年
            $lastStatisticCache = $lastStatistic; // 回退时，使用
            // 累计时长
            $lastStatistic['run_total_time'] = $lastStatistic['run_total_time'] + $lastEvent['run_time'];
            // 统计：X+1 年
            if (empty($newStatistic)) {
                // 无统计记录，新建一条
                $newStatistic = [
                    'station_id'     => $newEvent['station_id'],
                    'generator_id'   => $newEvent['generator_id'],
                    'year'           => $newEventYear,
                    'status'         => 1, // 静止
                    'run_at'         => 0,
                    'mnt_at'         => 0,
                    'run_num'        => 0,
                    'mnt_num'        => 0,
                    'run_total_time' => $newEvent['run_time'],
                    'mnt_total_time' => 0,
                ];
            } else {
                // 有统计记录，修改记录
                $newStatistic2 = $newStatistic; // 回退时，使用

                $newStatistic['status']         = 1; // 静止
                $newStatistic['run_total_time'] = $newStatistic['run_total_time'] + $newEvent['run_time'];
            }

            // 事件表 X+1
            $newEventId = $this->eventModel->newEventLog($newEvent);
            if ($newEventId === false) {
                return false;
            }
            // 事件表 X
            if ($this->eventModel->saveEventLog($lastEvent) === false) {
                // 回退
                $this->eventModel->delEventLogById($newEventId);
                return false;
            }

            // 统计表 X
            if ($this->statisticModel->saveStatistic($lastStatistic) === false) {
                // 回退
                $this->eventModel->saveEventLog($lastEventCache);
                $this->eventModel->delEventLogById($newEventId);
                return false;
            }

            // 统计表 X+1
            if ($this->statisticModel->saveStatistic($newStatistic) === false) {
                // 回退
                $this->statisticModel->saveStatistic($lastStatisticCache);
                $this->eventModel->saveEventLog($lastEventCache);
                $this->eventModel->delEventLogById($newEventId);
                return false;
            }
        }

        return true;
    }
}
