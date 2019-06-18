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

// 初始化爬虫
$spider = new Spider($config);




# ====================
# 回调钩子
# ====================

/**
 * 注意：
 * “回调”这个称呼实际上是有歧义的
 * 因为某些“回调”的返回值是会影响程序流程的
 * 这种类型的“回调”应该称为“处理器”更为妥当
 */

# ====================
# 回调
# ====================

# ====================
# 常规回调
# ====================

/**
 * 爬虫启动时调用
 * 常用于设置全局性的配置或初始化
 */
// $spider->on_start = function ($spider) {
//     Request::getDefaultHeader()->setHeaders([
//         'User-Agent' => UserAgent::USER_AGENTS['WIN10_X64_EDGE'],
//     ]);
//     // fiddler proxy
//     // Request::getDefaultOption()->proxy = 'http://127.0.0.1:8888';
// };

/**
 * 爬虫退出时调用
 * 常用于资源释放、清理
 */
// $spider->on_exit = function ($spider, $exit_code) {};

/**
 * 发起请求时调用
 * 常用于对响应内容进行修改
 */
// $spider->on_request = function ($spider, $request, $response) {};

/**
 * 在on_request后调用
 * 常用于针对返回的不同状态码进行反爬对策处理
 */
// $spider->on_status_code = function ($spider, $status_code, $request, $response) {};

/**
 * 在on_status_code后调用
 * 常用于对响应内容进行修改
 */
// $spider->on_process = function ($spider, $url, $request, $response) {};

/**
 * 发现一个入口url时调用
 * 常用于抓取非列表页+内容页结构站点时，预先生成列表页url
 */
// $spider->on_scan_url = function ($spider, $url, $request, $response) {};

/**
 * 发现一个列表页url时调用
 * 常用于抓取列表页结构松散站点时，预先生成内容页url
 */
// $spider->on_list_url = function ($spider, $url, $request, $response) {};

/**
 * 发现一个内容页url时调用
 * 常用于检测反爬、代理切换等反反爬对策
 */
// $spider->on_content_url = function ($spider, $url, $request, $response) {};

/**
 * 抓取到一个字段时调用
 * 常用于对字段进行二次处理、清洗
 */
// $spider->on_fetch_field = function ($spider, $name, $field) {};

/**
 * 抓取完一个页面所有字段时调用
 * 常用于对字段进行二次处理、清洗
 */
// $spider->on_fetch_page = function ($spider, $fields, $request, $response) {};

/**
 * 导出时调用
 * 常用于编写自定义导出器、导出规则，需要注意的是导出器使用的也是on_export回调，导出器和on_export不能同时使用
 */
// $spider->on_export = function ($spider, $export, $fields, $request, $response) {};

# ====================
# 异常回调
# ====================

/**
 * 程序触发错误时调用
 */
// $spider->on_error = function ($spider, $errno, $errstr, $errfile, $errline, $errcontext) {};

/**
 * 程序发生异常时调用
 */
// $spider->on_exception = function ($spider, $ex) {};

# ====================
# 高级回调
# ====================

/**
 * 请求功能覆写
 * 如爬虫内置的请求功能不适用，可编写自定义的功能覆写
 */
// $spider->requestOverride = function ($spider, $request) {};

/**
 * 发现url功能覆写
 * 如爬虫内置的url抓取不适用，可编写自定义的功能覆写
 */
// $spider->findUrlsOverride = function ($spider, $content, $request, $response) {};

/**
 * 在一个页面中发现url时调用
 * 常用于对发现url进行过滤、转换等处理
 */
// $spider->filterUrls = function ($spider, $urls) {};

/**
 * 当爬虫无法智能识别编码时调用
 * 用于自己处理编码
 */
// $spider->encodingHandler = function ($spider, $response) {};




# ====================
# 钩子
# ====================

# ====================
# 系统钩子
# ====================

/**
 * 发起请求前调用
 * 常用于对请求进行修改
 */
// $spider->hooks['beforeRequest'][] = function ($spider, $request) {};

/**
 * 爬虫退出前调用
 * 常用于资源释放、清理
 */
// $spider->hooks['beforeExit'][] = function ($spider, $exit_code) {};

# ====================



# ====================
# 插件加载
# ====================

# ====================
# 一般插件
# ====================

/**
 * Buff插件
 * 会带来好运吗？
 */
$spider->use(User\Plugins\Buff::class);

/**
 * 通用中间件管道插件
 */
// $spider->use(User\Plugins\Pipeline::class);

# ====================
# 导出器插件
# ====================

/**
 * CSV导出器
 * 适用于轻量级表格保存，不需要担心Excel格式兼容问题，适用于程序间数据交换等场景
 */
$spider->use(User\Plugins\CSVExporter::class);

/**
 * Excel导出器
 * 适用于需要报表、数据汇总、分析等场景
 */
$spider->use(User\Plugins\ExcelExporter::class);

/**
 * JSON导出器
 * 适用于轻量级数据保存
 */
$spider->use(User\Plugins\JsonExporter::class);

/**
 * 打印导出器
 * 用于调试阶段将数据直接打印出来方便调试，需要将UI关闭
 */
$spider->use(User\Plugins\PrintOutExporter::class);

# ====================
# 请求插件
# ====================

/**
 * Aria2 JSON-RPC客户端插件
 * 插件暂不支持加密传输，但支持token验证
 *
 * @param string $url      JSON-RPC Url
 * @param string $token    可选，token
 * @param string $savePath 可选，默认保存路径
 */
// $spider->use(User\Plugins\Aria2::class);

/**
 * 代理池插件
 *
 * @param string[] $proxies     代理IP列表
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

// 启动爬虫
$spider->start();
