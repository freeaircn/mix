<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2022-05-31 22:19:15
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-08 10:42:19
 */

namespace App\Libraries;

use Redis;
use RedisException;

class MyCache
{
    private $redis;

    private $config = [
        'host'    => '127.0.0.1',
        'port'    => 6379,
        'timeout' => 3,
        'expire'  => 86700, // 缓存过期时间 24小时5分钟
        //
        'prefix'  => '',
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);

        $this->redis = new Redis();
        try {
            $this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        } catch (RedisException $e) {
            throw new RedisException('Redis connect failed');
        }
    }

    // private function key(array $arguments = [])
    // {
    //     $args = '*';
    //     if (!empty($arguments)) {
    //         $args = md5(json_encode($arguments));
    //     }
    //     return strtolower(sprintf('%s_%s', $this->config['prefix'], $args));
    // }

    private function key(string $param = '*')
    {
        return strtolower(sprintf('%s-%s', $this->config['prefix'], $param));
    }

    public function setCache($param, $data)
    {
        $key = $this->key($param);

        try {
            $this->redis->set($key, json_encode($data), $this->config['expire']);
        } catch (RedisException $e) {
            throw new RedisException('Redis set failed');
        }
    }

    public function getCache($param)
    {
        $key = $this->key($param);

        $data = $this->redis->get($key);
        if ($data !== false) {
            $decodeData = json_decode($data, JSON_UNESCAPED_UNICODE);
            return $decodeData === null ? [] : $decodeData;
        } else {
            return [];
        }
    }

    public function delCache($param)
    {
        $key = $this->key($param);

        return $this->redis->del($key);
    }

    public function setExpire(int $time = 0)
    {
        $this->config['expire'] = $time;
    }

    public function setPrefix(string $prefix = '')
    {
        $this->config['prefix'] = $prefix;
    }

    public function ping()
    {
        return $this->redis->ping();
    }

    public function getTimeout()
    {
        return $this->redis->getTimeout();
    }

}
