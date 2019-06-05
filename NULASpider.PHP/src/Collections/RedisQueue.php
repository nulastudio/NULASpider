<?php

// namespace nulastudio\Collections;

// use CSRedis\CSRedisClient;
// use nulastudio\Collections\QueueInterface;
// use nulastudio\Threading\LockManager;

// class RedisQueue implements QueueInterface
// {
//     private $lock = '';

//     private $CSRedis;
//     private $key;

//     public function __construct($connString, array $config = [])
//     {
//         $this->lock = strtoupper(md5(uniqid(mt_rand(), true)));

//         // 连接字符串转换
//         $transConfig   = $this->transConfig($connString, $config);
//         $this->CSRedis = new CSRedisClient($transConfig);
//     }

//     private function transConfig($connString, array $config = [])
//     {
//         #warning connString TODO

//         $host   = '';
//         $params = [];

//         // host提取
//         if (isset($config['host'])) {
//             $host = $config['host'];
//             unset($config['host']);
//         }

//         // key提取
//         if (isset($config['key'])) {
//             $this->key = $config['key'];
//             unset($config['key']);
//         }

//         // params拼接
//         foreach ($config as $key => $value) {
//             $params[] = "{$key}={$value}";
//         }

//         return implode(',', array_merge([$host], $params));
//     }

//     public function pop()
//     {
//         $length = $this->count();
//         if (!$length) {
//             throw new QueueException('The Queue is empty.');
//         }
//         LockManager::getLock($this->lock);
//         $val = null;
//         try {
//             $val = $this->CSRedis->LPop($this->key);
//         } catch (\Exception $e) {
//             throw $e;
//         } finally {
//             LockManager::releaseLock($this->lock);
//         }
//         return $val;
//     }
//     public function push($value)
//     {
//         LockManager::getLock($this->lock);
//         try {
//             $this->CSRedis->RPush<string>($this->key, $value);
//         } catch (\Exception $e) {
//             throw $e;
//         } finally {
//             LockManager::releaseLock($this->lock);
//         }
//     }
//     public function exists($value)
//     {
//         #warning TODO
//         return false;
//     }
//     public function peek()
//     {
//         $length = $this->count();
//         if (!$length) {
//             throw new QueueException('The Queue is empty.');
//         }
//         LockManager::getLock($this->lock);
//         $val = null;
//         try {
//             $val = $this->CSRedis->LIndex($length - 1);
//         } catch (\Exception $e) {
//             throw $e;
//         } finally {
//             LockManager::releaseLock($this->lock);
//         }
//         return $val;
//     }
//     public function count()
//     {
//         LockManager::getLock($this->lock);
//         $val = null;
//         try {
//             $val = $this->CSRedis->LLen($this->key);
//         } catch (\Exception $e) {
//             throw $e;
//         } finally {
//             LockManager::releaseLock($this->lock);
//         }
//         return $val;
//     }
//     function empty() {
//         $this->CSRedis->Del($this->key);
//     }
// }
