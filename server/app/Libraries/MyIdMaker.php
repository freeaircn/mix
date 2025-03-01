<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-06 01:17:02
 * @LastEditors: freeair
 * @LastEditTime: 2023-10-22 19:27:10
 */

namespace App\Libraries;

use Godruoyi\Snowflake\RedisSequenceResolver;
use Godruoyi\Snowflake\Snowflake;
use Redis;
use RedisException;

class MyIdMaker
{
    /**
     * Redis - Uid
     */
    public $redisHost;
    public $redisPort;
    public $redisTimeOut;
    public $dataCenter;
    public $workerId;

    public function __construct()
    {
        $this->redisHost    = '127.0.0.1';
        $this->redisPort    = 6379;
        $this->redisTimeOut = 2;
        //
        $this->dataCenter       = 1;
        $this->workerId         = 1;
        $this->defaultStartTime = '2011-01-01 00:00:00';
    }

    /**
     * 申请Id，采用雪花算法id结构，redis存储序列
     *
     * @author freeair
     * @DateTime 2022-03-24
     * @param string $cacheKey
     * @return mixed - bool or int
     */
    public function apply(string $cacheKey = '')
    {
        $redis = new Redis();

        try {
            $redis->connect($this->redisHost, $this->redisPort, $this->redisTimeOut);
            $seqResolver = new RedisSequenceResolver($redis);
        } catch (RedisException $e) {
            return false;
        }

        $seqResolver->setCachePrefix($cacheKey);
        $snowflake = new Snowflake($this->dataCenter, $this->workerId);
        $snowflake->setStartTimeStamp(strtotime($this->defaultStartTime) * 1000);
        $snowflake->setSequenceResolver($seqResolver);

        return $snowflake->id();
    }

    public function parse(int $Uid = 0)
    {
        $snowflake = new Snowflake($this->dataCenter, $this->workerId);
        $snowflake->setStartTimeStamp(strtotime($this->defaultStartTime) * 1000);
        return $snowflake->parseId($Uid, true);
    }
}
