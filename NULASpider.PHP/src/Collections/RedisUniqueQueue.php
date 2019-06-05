<?php

namespace nulastudio\Collections;

use CSRedis\CSRedisClient;
use nulastudio\Collections\QueueInterface;
use nulastudio\Threading\LockManager;
use nulastudio\Collections\QueueException;

class RedisUniqueQueue implements QueueInterface
{
    private $lock = '';

    private $CSRedis;
    private $key;

    private $maxTime = 0;

    public function __construct($connString, array $config = [])
    {
        $this->lock = strtoupper(md5(uniqid(mt_rand(), true)));

        // 连接字符串转换
        $transConfig   = $this->transConfig($connString, $config);
        $this->CSRedis = new CSRedisClient($transConfig);

        if (!$this->key) {
            throw new QueueException('You must specify a key.');
        }

        // 查询时间
        // ValueTuple<string, double>
        $maxTime = $this->CSRedis->ZRangeWithScores($this->key, 0, 0);
        \HybridUtil::dump($maxTime);
        if (empty($maxTime)) {
            newTime:
            $maxTime = time();
        } else {
            $maxTime = (int)$maxTime[0].Item1;
            if (!$maxTime) {
                goto newTime;
            }
        }
        $this->maxTime = $maxTime;
    }

    private function transConfig($connString, array $config = [])
    {
        #warning connString TODO

        $host   = '';
        $params = [];

        // host提取
        if (isset($config['host'])) {
            $host = $config['host'];
            unset($config['host']);
        }

        // key提取
        if (isset($config['key'])) {
            $this->key = $config['key'];
            unset($config['key']);
        }

        // params拼接
        foreach ($config as $key => $value) {
            $params[] = "{$key}={$value}";
        }

        return implode(',', array_merge([$host], $params));
    }

    public function pop()
    {
        $length = $this->count();
        if (!$length) {
            throw new QueueException('The Queue is empty.');
        }
        $value = $this->peek();
        LockManager::getLock($this->lock);
        try {
            $this->CSRedis->ZRem<string>($this->key, $value);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->lock);
        }
        return $value;
    }
    public function push($value)
    {
        LockManager::getLock($this->lock);
        try {
            $this->CSRedis->ZAdd($this->key, \System\ValueTuple::Create<\System\Double,\System\Object>((double)++$this->maxTime, \HybridUtil::toObject($value)));
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->lock);
        }
    }
    public function exists($value)
    {
        LockManager::getLock($this->lock);
        $val = null;
        try {
            $val = $this->CSRedis->ZRank($this->key, \HybridUtil::toObject($value)) !== null;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->lock);
        }
        return $val;
    }
    public function peek()
    {
        $length = $this->count();
        if (!$length) {
            throw new QueueException('The Queue is empty.');
        }
        LockManager::getLock($this->lock);
        $val = null;
        try {
            $val = $this->CSRedis->ZRange($this->key, 0, 0)[0];
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->lock);
        }
        return $val;
    }
    public function count()
    {
        LockManager::getLock($this->lock);
        $val = null;
        try {
            $val = $this->CSRedis->ZCard($this->key);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            LockManager::releaseLock($this->lock);
        }
        return $val;
    }
    public function empty() {
        $this->CSRedis->Del($this->key);
    }
}
