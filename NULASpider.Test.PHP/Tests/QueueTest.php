<?php

// $segments = nulastudio\Util\UriUtil::parseUrl('redis://:@127.0.0.1:8080/db0?charset=uft8&timeout=10&message=你好#aaa');
// var_dump($segments);

// $segments = nulastudio\Util\RedisHelper::parseConnectionString('redis://:@127.0.0.1:8080/db0?charset=uft8&timeout=10&message=你好#aaa');
// var_dump($segments);

$q = new nulastudio\Collections\RedisQueue('redis://127.0.0.1/db0?key=testUrlQueue&password=&prefix=pre_');
$q = new nulastudio\Collections\ConcurrentRedisQueue('redis://127.0.0.1/db0?key=testUrlQueue&password=&prefix=pre_');
$q = new nulastudio\Collections\RedisUniqueQueue('redis://127.0.0.1/db0?key=testUrlQueue&password=&prefix=pre_');
$q = new nulastudio\Collections\ConcurrentRedisUniqueQueue('redis://127.0.0.1/db0?key=testUrlQueue&password=&prefix=pre_');
$q = new nulastudio\Collections\MemoryQueue();
$q = new nulastudio\Collections\ConcurrentMemoryQueue();
$q = new nulastudio\Collections\UniqueQueue();
$q = new nulastudio\Collections\ConcurrentUniqueQueue();

$redisQueue = new nulastudio\Collections\RedisQueue(
    'redis://127.0.0.1/db0?key=testUrlQueue&password=&prefix=pre_'
);

var_dump($redisQueue->exists('hello'));

var_dump($redisQueue->pop());

$redisQueue->push('你好');

$redisQueue->push(null);
$redisQueue->push('');

var_dump($redisQueue->exists(null));
var_dump($redisQueue->exists(''));

$redisQueue->push('你好');

var_dump($redisQueue->exists('你好'));

$redisQueue->push('hello');

var_dump($redisQueue->count());

var_dump($redisQueue->peek());

var_dump($redisQueue->pop());
var_dump($redisQueue->pop());
var_dump($redisQueue->pop());

var_dump($redisQueue->count());

var_dump($redisQueue->exists('你好'));

var_dump($redisQueue->empty());
