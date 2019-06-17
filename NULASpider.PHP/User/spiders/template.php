<?php

use nulastudio\Collections\ConcurrentMemoryQueue;
use nulastudio\Collections\UniqueQueue;
use nulastudio\Log\FileLogger;
use nulastudio\Spider\Spider;

# ====================
# 配置
# ====================

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'logger'              => new FileLogger(DIR_LOG . '/' . date('Y-m-d') . '.log'),
    'input_encoding'      => 'smart', // GIVEN_ENCODING, "auto", "smart", "handler"
    'fallback_encoding'   => '',
    'output_encoding'     => 'auto', // unsupported yet, always "UTF-8"
    'urlQueue'            => new UniqueQueue(),
    'downloadQueue'       => new ConcurrentMemoryQueue(),
    'processQueue'        => new ConcurrentMemoryQueue(),
    'scan_urls'           => [],
    'list_url_pattern'    => [],
    'content_url_pattern' => [],
    'fields'              => [],
    'export'              => [],
];

# ====================

$spider = new Spider($config);

# ====================
# 回调钩子
# ====================

// $spider->on_start = function ($spider) {
//     Request::getDefaultHeader()->setHeaders([
//         'User-Agent' => UserAgent::USER_AGENTS['WIN10_X64_EDGE'],
//     ]);
//     // fiddler proxy
//     // Request::getDefaultOption()->proxy = 'http://127.0.0.1:8888';
// };

// 常规回调
// $spider->on_exit = function ($spider, $exit_code) {};
// $spider->on_request = function ($spider, $request, $response) {};
// $spider->on_status_code = function ($spider, $status_code, $request, $response) {};
// $spider->on_process = function ($spider, $url, $request, $response) {};
// $spider->on_scan_url = function ($spider, $url, $request, $response) {};
// $spider->on_list_url = function ($spider, $url, $request, $response) {};
// $spider->on_content_url = function ($spider, $url, $request, $response) {};
// $spider->on_fetch_field = function ($spider, $name, $field) {};
// $spider->on_fetch_page = function ($spider, $fields, $request, $response) {};
// $spider->on_export = function ($spider, $export, $fields, $request, $response) {};

// 异常回调
// $spider->on_error = function ($spider, $errno, $errstr, $errfile, $errline, $errcontext) {};
// $spider->on_exception = function ($spider, $ex) {};

// 高级回调
// $spider->requestOverride = function ($spider, $request) {};
// $spider->findUrlsOverride = function ($spider, $content, $request, $response) {};
// $spider->filterUrls = function ($spider, $urls) {};
// $spider->encodingHandler = function ($spider, $response) {};

// 系统钩子
// $spider->hooks['beforeRequest'][] = function ($spider, $request) {};
// $spider->hooks['beforeExit'][] = function ($spider, $exit_code) {};

# ====================

# ====================
# 插件加载
# ====================

/**
 * Buff插件
 * 会带来好运吗？
 */
$spider->use(User\Plugins\Buff::class);

// $spider->use(User\Plugins\Pipeline::class);

# ====================
# 导出器插件
# ====================

// $spider->use(User\Plugins\CSVExporter::class);
// $spider->use(User\Plugins\ExcelExporter::class);
// $spider->use(User\Plugins\JsonExporter::class);
// $spider->use(User\Plugins\PrintOutExporter::class);

# ====================
# 请求插件
# ====================

$spider->use(User\Plugins\Aria2::class);

/**
 * 代理池插件
 *
 * @param string[] $proxies     可选，代理IP列表
 * @param bool     $enable      可选，是否立即启用代理池，默认否
 * @param bool     $intelligent 可选，是否智能选择优先代理
 * @param number   $CD          可选，代理冷却时间
 */
// $spider->use(User\Plugins\ProxyPool::class);

/**
 * 随机User-Agent头部插件
 *
 * @param string[] $userUAs 可选，如果提供则从$userUAs中随机。
 */

/*
// 下面这段配置相当于每次请求时切换随机的UA
$spider->use(User\Plugins\RandomUserAgent::class);

// 下面这段配置相当于每次请求时切换不同设备类型（如果网站识别UA的话）
$spider->use(User\Plugins\RandomUserAgent::class, [
// PC
'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36',
// Android
'Mozilla/5.0 (Linux; Android 4.2.0; Nexus 10 Build/JOP24G) AppleWebKit/537.51.1 (KHTML, like Gecko) Chrome/51.0.2704.79 Mobile Safari/537.36',
// IOS
'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Mobile/14G60 MicroMessenger/6.6.1 NetType/WIFI Language/zh_HK',
]);
 */

# ====================

$spider->start();
