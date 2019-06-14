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
    'scan_urls'           => [
        'https://news.ycombinator.com/item?id=14936025',
    ],
    'list_url_pattern'    => [
        '#https://news.ycombinator.com/news\?p=\d+#',
    ],
    'content_url_pattern' => [
        '#https://news.ycombinator.com/item\?id=\d+#',
    ],
    'fields'              => [
        'title' => [
            'type' => 'css',
            'selector' => '.title>a@innerText',
        ],
        'comments' => [
            'type' => 'css',
            'selector' => '.comment-tree>tr',
            'fields'=>[
                'ind'=>[
                    'type'=>'css',
                    'selector'=> '.ind>img@width',
                ],
                'author' => [
                    'type' => 'css',
                    'selector' => '.hnuser@innerText',
                ],
                'time' => [
                    'type' => 'css',
                    'selector' => '.age>a@innerText',
                ],
                'comment' => [
                    'type' => 'css',
                    'selector' => '.comment@innerText',
                ],
            ],
            'repeated' => true,
        ],
    ],
    'export'              => [
        'type'=>'print',
    ],
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

$spider->use(User\Plugins\Buff::class);

// $spider->use(User\Plugins\CSVExporter::class);
// $spider->use(User\Plugins\ExcelExporter::class);
// $spider->use(User\Plugins\JsonExporter::class);
// $spider->use(User\Plugins\PrintOutExporter::class);

// $spider->use(User\Plugins\Pipeline::class);
// $spider->use(User\Plugins\ProxyPool::class);
// $spider->use(User\Plugins\RandomUserAgent::class);

# ====================

$spider->start();
