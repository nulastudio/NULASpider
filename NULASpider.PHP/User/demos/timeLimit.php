<?php

use nulastudio\Collections\ConcurrentMemoryQueue;
use nulastudio\Collections\ConcurrentUniqueQueue;
use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'requestLimit'        => 3000,
    'processLimit'        => 5000,
    'urlQueue'            => new ConcurrentUniqueQueue(),
    'downloadQueue'       => new ConcurrentMemoryQueue(),
    'processQueue'        => new ConcurrentMemoryQueue(),
    'scan_urls'           => [
        'http://localhost:8081/articles',
    ],
    'list_url_pattern'    => [
        '#http://localhost:8081/articles/\d+#',
    ],
    'content_url_pattern' => [
        '#http://localhost:8081/article/\d+#',
    ],
];

$spider = new Spider($config);

$timer1 = 0;
$timer2 = 0;

$spider->on_start = function ($spider) use (&$timer1, &$timer2) {
    $timer1 = microtime(true);
    $timer2 = microtime(true);
};

/**
 * 如果有timeLimit回调就不读配置里的requestLimit和processLimit，否则读
 * timeLimit回调的返回值表示需要等待多少时间，单位毫秒，requestLimit和processLimit一样
 * 
 * timeLimit的实现仅仅是运行任务前的简单睡眠，而且受影响于爬虫逻辑代码（特别是网络）的运行
 * 运行间隔时间不一定严格等同于设定值，但能确保的是必然不会低于设定值
 * 也就是说你设定了3秒钟间隔，它就绝不可能在3秒内跑过去
 * 
 */
$spider->timeLimit = function ($spider, $type, $url) {
    $timeLimit = mt_rand(2000, 5000);
    echo "[{$type}] [{$url}] requesting timeLimit, get {$timeLimit} ms.\n";
    return $timeLimit;
};

$spider->on_request = function ($spider, $request, $response) use (&$timer1) {
    $t = microtime(true) - $timer1;
    $timer1 = microtime(true);
    echo "request time: {$t} ms.\n";
    return true;
};

$spider->on_fetch_page = function ($spider, $fields, $request, $response) use (&$timer2) {
    $t = microtime(true) - $timer2;
    $timer2 = microtime(true);
    echo "process time: {$t} ms.\n";
    return $fields;
};

$spider->start();
