<?php

namespace nulastudio\Spider;

use liesauer\SimpleHttpClient;
use nulastudio\Log\NullLogger;
use nulastudio\Networking\Http\Request;
use nulastudio\Networking\Http\Response;
use nulastudio\Spider\Application;
use nulastudio\Spider\ConcurrentQueue;
use nulastudio\Spider\Exceptions\SpiderException;
use nulastudio\Spider\ServiceProviders\ExporterServiceProvider;
use nulastudio\Spider\ServiceProviders\HookServiceProvider;
use nulastudio\Spider\ServiceProviders\PluginServiceProvider;
use nulastudio\Threading\LockManager;
use nulastudio\Util;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

class Spider
{
    // 去重url数组
    private $unique_urls = [];

    private $lockManager;

    // 请求队列
    private $downloadQueue;
    // 处理队列
    private $processQueue;

    private $kernel;

    // 监视器
    // 用于存放外部可能需要的监控数据
    private $monitor = [
        'downloaded' => 0,
        'processed'  => 0,
    ];

    // 钩子挂接点
    private $hook_points = [
        'beforeRequest',
        'beforeExit',
    ];

    // 回调函数以及功能覆写
    private $callbacks = [
        'on_start'          => null,
        'on_exit'           => null,
        'on_exception'      => null,
        'on_request'        => null,
        'on_status_code'    => null,
        'on_process'        => null,
        'on_scan_url'       => null,
        'on_list_url'       => null,
        'on_content_url'    => null,
        'on_fetch_field'    => null,
        'on_fetch_page'     => null,
        'on_export'         => null,
        'requestOverride'   => null,
        'findUrlsOverride'  => null,
        'storeDataOverride' => null,
    ];

    // 爬虫配置项
    private $configs = [];

    // logger
    use LoggerTrait;
    use LoggerAwareTrait;

    public function __construct(array $configs = [])
    {
        $this->lockManager = new LockManager;
        LockManager::init();

        // 初始化请求队列、处理队列
        $this->downloadQueue = new ConcurrentQueue(Request::class);
        $this->processQueue  = new ConcurrentQueue(Response::class);

        // 接收并检查配置
        $this->checkConfig($configs);

        $this->setLogger($this->configs['logger']);

        // 注册内核
        $this->kernel = new Kernel($this, [
            HookServiceProvider::class,
            PluginServiceProvider::class,
            ExporterServiceProvider::class,
        ]);
        $this->kernel->bootstrap();
    }

    public function __get($prop)
    {
        // readonly
        switch ($prop) {
            case 'configs':
                return $this->configs;
            case 'monitor':
                return $this->monitor;
            case 'hook_points':
                return $this->hook_points;
            // case 'callbacks':
            //     return $this->callbacks;
            default:
                // 返回回调函数
                // must be callable or null
                LockManager::getLock('callbacks_accessor');
                $callback = false;
                if (array_key_exists($prop, $this->callbacks)) {
                    $callback = $this->callbacks[$prop];
                    // if not callable, set to null
                    if (!is_callable($callback)) {
                        $callback = null;
                    }
                }
                LockManager::releaseLock('callbacks_accessor');
                if ($callback !== false) {
                    return $callback;
                }
                // 返回注册服务
                return $this->kernel->getService($prop);
        }
    }

    public function __set($prop, $val)
    {
        // 只有回调函数可写
        if (array_key_exists($prop, $this->callbacks)) {
            if (($callable = Util\resolveCallable($val, true)) !== false) {
                $this->callbacks[$prop] = $callable;
                return;
            }
        }
        throw new SpiderException('Attempt to write a readonly property.');
    }

    public function __call($name, $args)
    {
        return ($this->kernel->getService($name))($this, ...$args);
    }

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

    private function checkConfig($configs)
    {
        $default_configs = [
            'thread'              => 5,
            'logger'              => new NullLogger,
            'scan_urls'           => [],
            'list_url_pattern'    => [],
            'content_url_pattern' => [],
            'fields'              => [],
            'export'              => [],
        ];
        $this->configs = array_replace_recursive($default_configs, $configs);
    }

    public function start()
    {
        define('BOOT_UP_TIME_FLOAT', microtime(true));

        set_error_handler([$this, 'error_handler'], error_reporting());
        set_exception_handler([$this, 'exception_handler']);

        $this->callback('on_start', $this);

        $this->initWorker();

        Application::run($this);

        // 安全退出
        $this->safeExit();
    }

    private function initWorker()
    {
        foreach ($this->configs['scan_urls'] as $scan_url) {
            $this->add_url($scan_url);
        }
    }

    public function add_url($url, $prevUrl = null)
    {
        try {
            LockManager::getLock('add_url');
            $url_hash = md5($url);
            if (!Util\in_array($url_hash, $this->unique_urls)) {
                $this->unique_urls[] = $url_hash;
                $request             = new Request(Request::REQUEST_METHOD_GET, $url);
                if ($prevUrl) {
                    $request->setHeader('Referer', $prevUrl);
                }
                $this->downloadQueue->Enqueue($request);
            }
        } finally {
            LockManager::releaseLock('add_url');
        }
    }

    public function get_url()
    {
        return $this->downloadQueue->Dequeue();
    }
    public function has_url()
    {
        return $this->downloadQueue->Count() !== 0;
    }

    public function get_response()
    {
        return $this->processQueue->Dequeue();
    }
    public function has_response()
    {
        return $this->processQueue->Count() !== 0;
    }

    public function fetch_url($request)
    {
        $this->hook('beforeRequest', $this, $request);

        $response = null;
        if ($this->requestOverride) {
            $response = $this->callback('requestOverride', $this, $request);
        } else {
            $method   = $request->getMethod();
            $url      = $request->getUrl();
            $header   = $request->getAllHeaders();
            $cookie   = '';
            $data     = $request->getData();
            $options  = $request->getOption();
            $response = null;
            if ($method === Request::REQUEST_METHOD_GET) {
                $response = SimpleHttpClient::quickGet($url, $header, $cookie, $data);
            } else if ($method === Request::REQUEST_METHOD_POST) {
                $response = SimpleHttpClient::quickPost($url, $header, $cookie, $data);
            }
            $response = Response::fromSHCResponse($response)->setRequest($request);
        }

        if ($this->on_request) {
            $ret = $this->callback('on_request', $this, $request, $response);
            if ($ret === false) {
                return;
            } else if ($ret instanceof Response) {
                $response = $ret;
            }
        }

        if ($this->on_status_code) {
            $status_code = $response->getHttpCode();
            $ret         = $this->callback('on_status_code', $this, $status_code, $request, $response);
            if ($ret === false) {
                return;
            } else if ($ret instanceof Response) {
                $response = $ret;
            }
        }

        if ($this->on_process) {
            $url = $request->getUrl();
            $ret = $this->callback('on_process', $this, $url, $request, $response);
            if ($ret === false) {
                return;
            }
        }

        LockManager::getLock('update_downloaded');
        $this->monitor['downloaded']++;
        LockManager::releaseLock('update_downloaded');
        $this->processQueue->Enqueue($response);
    }

    public function precess_response($response)
    {
        $request = $response->getRequest();
        $url     = $request->getUrl();
        $content = $response->getRawContent();
        if ($this->is_scan_url($url) && $this->on_scan_url) {
            $ret = $this->callback('on_scan_url', $this, $url, $request, $response);
            if ($ret !== true) {
                return;
            }
        }
        if ($this->is_list_url($url) && $this->on_list_url) {
            $ret = $this->callback('on_list_url', $this, $url, $request, $response);
            if ($ret !== true) {
                return;
            }
        }
        if ($this->is_content_url($url)) {
            if ($this->on_content_url) {
                $ret = $this->callback('on_content_url', $this, $url, $request, $response);
                if ($ret !== true) {
                    return;
                }
            }
            $result = $this->fetch_fields($this->configs['fields'], $content, $request, $response);
            if ($this->on_fetch_page) {
                $result = $this->callback('on_fetch_page', $this, $result, $request, $response);
                if ($result === false) {
                    return;
                }
            }
            if ($this->on_export) {
                $this->callback('on_export', $this, $this->configs['export'], $result, $request, $response);
            }
        }
        $this->find_list_url($content, $request, $response);
        $this->find_content_url($content, $request, $response);

        LockManager::getLock('update_processed');
        $this->monitor['processed']++;
        LockManager::releaseLock('update_processed');
    }

    public function is_scan_url($url)
    {
        return $this->is_url_matches_pattern($url, $this->configs['scan_urls']) !== false;
    }
    public function is_list_url($url)
    {
        return $this->is_url_matches_pattern($url, $this->configs['list_url_pattern']) !== false;
    }
    public function is_content_url($url)
    {
        return $this->is_url_matches_pattern($url, $this->configs['content_url_pattern']) !== false;
    }
    private function is_url_matches_pattern($url, $pattern)
    {
        function is_regex($pattern)
        {
            if (!is_string($pattern)) {
                return false;
            }
            return preg_match('/^[^\da-zA-Z\s].*[^\da-zA-Z\s][a-zA-Z]*$/', $pattern) === 1;
        }
        if (empty($url) || empty($pattern) || !is_string($url)) {
            return false;
        }
        $patterns        = is_array($pattern) ? $pattern : [$pattern];
        $matched_pattern = false;
        foreach ($patterns as $patt) {
            if (is_string($patt)) {
                if (is_regex($patt) ? (preg_match($patt, $url) === 1) : ($url === $patt)) {
                    $matched_pattern = $patt;
                    break;
                }
            } else if (is_callable($patt)) {
                if (call_user_func($patt, $url) === true) {
                    $matched_pattern = $patt;
                    break;
                }
            }
        }
        return $matched_pattern;
    }

    private function find_list_url($content, $request, $response)
    {
        $prevUrl = $request->getUrl();
        $urls    = [];
        if ($this->findUrlsOverride) {
            $ret = $this->callback('findUrlsOverride', $this, $content, $request, $response);
            if (is_array($ret)) {
                $urls = $ret;
            }
        } else {
            $urls = $this->find_urls($content, $request, $response);
        }
        foreach ($urls as $url) {
            $url = Util\absolute_url($prevUrl, $url);
            if ($this->is_list_url($url)) {
                $this->add_url($url, $prevUrl);
            }
        }
    }

    private function find_content_url($content, $request, $response)
    {
        $prevUrl = $request->getUrl();
        $urls    = [];
        if ($this->findUrlsOverride) {
            $ret = $this->callback('findUrlsOverride', $this, $content, $request, $response);
            if (is_array($ret)) {
                $urls = $ret;
            }
        } else {
            $urls = $this->find_urls($content, $request, $response);
        }
        foreach ($urls as $url) {
            $url = Util\absolute_url($prevUrl, $url);
            if ($this->is_content_url($url)) {
                $this->add_url($url, $prevUrl);
            }
        }
    }

    private function find_urls($content, $request, $response)
    {
        $urls = [];
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            if ($document->DocumentNode) {
                $nodes = $document->DocumentNode->SelectNodes("//a[@href]") ?? [];
                foreach ($nodes as $node) {
                    // $urls[] = $node->Attributes["href"]->Value;
                    $val            = $node->Attributes->get_Item('href')->Value;
                    $val && $urls[] = $val;
                }
            }
        } catch (\Exception $e) {}
        return $urls;
    }

    private function fetch_single_field($type, $selector, $content, $request, $response)
    {
        if ($type === 'xpath') {
            return $this->fetch_single_field_xpath($selector, $content, $request, $response);
        } else if ($type === 'regex') {
            return $this->fetch_single_field_regex($selector, $content, $request, $response);
        } else if ($type === 'css') {
            return $this->fetch_single_field_css($selector, $content, $request, $response);
        } else if ($type === 'callback') {
            return $this->fetch_single_field_callback($selector, $content, $request, $response);
        } else if ($type === 'raw') {
            return $this->fetch_single_field_raw($selector, $content, $request, $response);
        }
        throw new SpiderException("Unrecognized selector type: {$type}.");
    }
    private function fetch_repeated_fields($type, $selector, $content, $request, $response)
    {
        if ($type === 'xpath') {
            return $this->fetch_repeated_fields_xpath($selector, $content, $request, $response);
        } else if ($type === 'regex') {
            return $this->fetch_repeated_fields_regex($selector, $content, $request, $response);
        } else if ($type === 'css') {
            return $this->fetch_repeated_fields_css($selector, $content, $request, $response);
        } else if ($type === 'callback') {
            return $this->fetch_repeated_fields_callback($selector, $content, $request, $response);
        } else if ($type === 'raw') {
            return $this->fetch_repeated_fields_raw($selector, $content, $request, $response);
        }
        throw new SpiderException("Unrecognized selector type: {$type}.");
    }
    private function fetch_single_field_xpath(string $selector, string $content, $request, $response)
    {
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            /**
             * 必须使用纯粹的node节点选择器去获取节点
             * 否则在某些时候获取到的并不是期望的
             */
            $node = $document->DocumentNode->SelectSingleNode(Util\pureXpath($selector));
            if ($node) {
                $parts = explode('/', $selector);
                $attr  = $parts[count($parts) - 1];
                if ($attr{0} === '@') {
                    /* attr */
                    $name = substr($attr, 1);
                    return $node->Attributes->get_Item($name)->Value;
                } else if ($attr === 'text()') {
                    /* text */
                    return $node->InnerText;
                }
                /* html */
                return $node->InnerHtml;
            }
        } catch (\Exception $e) {}
    }
    private function fetch_repeated_fields_xpath(string $selector, string $content, $request, $response)
    {
        $result = [];
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            /**
             * 必须使用纯粹的node节点选择器去获取节点
             * 否则在某些时候获取到的并不是期望的
             */
            $nodes = $document->DocumentNode->SelectNodes(Util\pureXpath($selector));
            if ($nodes) {
                foreach ($nodes as $node) {
                    $parts = explode('/', $selector);
                    $attr  = $parts[count($parts) - 1];
                    if ($attr{0} === '@') {
                        /* attr */
                        $name     = substr($attr, 1);
                        $result[] = $node->Attributes->get_Item($name)->Value;
                    } else if ($attr === 'text()') {
                        /* text */
                        $result[] = $node->InnerText;
                    }
                    /* html */
                    $result[] = $node->InnerHtml;
                }
            }
        } catch (\Exception $e) {}
        return $result;
    }
    private function fetch_single_field_regex(string $selector, string $content, $request, $response)
    {
        if (Util\is_regex($selector)) {
            if (preg_match($selector, $content, $matches) === 1) {
                return $matches[0];
            }
        }
    }
    private function fetch_repeated_fields_regex(string $selector, string $content, $request, $response)
    {
        $result = [];
        // 如果$matches数组只有一个元素，则表示无分组，使用匹配到的全文作为结果
        // 如果$matches数组有多个元素，则表示有分组，强制使用第一个分组作为结果
        if (Util\is_regex($selector)) {
            if (preg_match_all($selector, $content, $matches)) {
                if (count($matches) == 1) {
                    $result = array_values($matches);
                } else {
                    $result = array_values($matches[array_keys($matches)[1]]);
                }
            }
        }
        return $result;
    }
    private function fetch_single_field_css(string $selector, string $content, $request, $response)
    {
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            /**
             * css选择器是节点级的，无法获取节点的属性值
             */

            // $node = $document->DocumentNode->QuerySelectorAll($selector);
            $node = \Fizzler\Systems\HtmlAgilityPack\HtmlNodeSelection::QuerySelector($document->DocumentNode, $selector);
            if ($node) {
                /* html */
                return $node->InnerHtml;
            }
        } catch (\Exception $e) {}
    }
    private function fetch_repeated_fields_css(string $selector, string $content, $request, $response)
    {
        $result = [];
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            /**
             * css选择器是节点级的，无法获取节点的属性值
             */

            // $nodes = $document->DocumentNode->QuerySelectorAll($selector);
            $nodes = \Fizzler\Systems\HtmlAgilityPack\HtmlNodeSelection::QuerySelectorAll($document->DocumentNode, $selector);
            if ($nodes) {
                foreach ($nodes as $node) {
                    /* html */
                    $result[] = $node->InnerHtml;
                }
            }
        } catch (\Exception $e) {}
        return $result;
    }
    private function fetch_single_field_callback(callable $callback, string $content, $request, $response)
    {
        return call_user_func($callback, $content, $this, $request, $response);
    }
    private function fetch_repeated_fields_callback(callable $callback, string $content, $request, $response)
    {
        $ret = call_user_func($callback, $content, $this, $request, $response);
        // 强制包装成数组，保证结构不被破坏
        if (!is_array($ret)) {
            $ret = [$ret];
        }
        return $ret;
    }
    private function fetch_single_field_raw($selector, $content, $request, $response)
    {
        return $content;
    }
    private function fetch_repeated_fields_raw($selector, $content, $request, $response)
    {
        $res = $content;
        // 强制包装成数组，保证结构不被破坏
        if (!is_array($res)) {
            $res = [$res];
        }
        return $res;
    }

    private function fetch_fields($fields, $content, $request, $response, $recursive = false)
    {
        $result = [];
        foreach ($fields as $name => $selector) {
            $field = null;
            if (is_string($selector)) {
                /**
                 * 简单xpath
                 */
                $field = $this->fetch_single_field_xpath($selector, $content, $request, $response);
            } else if (is_array($selector)) {
                /**
                 * 数组
                 * source可选，当设置了source时，$content将被覆写，以下为有效的source：Request、Response、callback
                 * 当source为Request时，可同时设置is_ajax、auto_referer
                 * selector必需（当type为raw时可省略）
                 * type可选，支持xpath、css、regex、callback、raw，默认为xpath
                 * callback可选
                 * fields可选，表示嵌套
                 * repeated可选，表示可重复
                 */
                $_source = $selector['source'] ?? '';
                if ($_source) {
                    if (is_callable($_source)) {
                        $content = call_user_func($_source, $this, $request, $response);
                    } else if ($_source instanceof Request) {
                        $_is_ajax      = $selector['is_ajax'] ?? false;
                        $_auto_referer = $selector['auto_referer'] ?? false;
                        if ($_is_ajax) {
                            $_source->setHeader('X-Requested-With', 'XMLHttpRequest');
                        }
                        if ($_auto_referer) {
                            $_source->setHeader('Referer', $request->getUrl());
                        }
                        $method    = $_source->getMethod();
                        $url       = $_source->getUrl();
                        $header    = $_source->getAllHeaders();
                        $cookie    = '';
                        $data      = $_source->getData();
                        $options   = $_source->getOption();
                        $_response = null;
                        if ($method === Request::REQUEST_METHOD_GET) {
                            $_response = SimpleHttpClient::quickGet($url, $header, $cookie, $data);
                        } else if ($method === Request::REQUEST_METHOD_POST) {
                            $_response = SimpleHttpClient::quickPost($url, $header, $cookie, $data);
                        }
                        $content = Response::fromSHCResponse($_response)->setRequest($request)->getRawContent();
                    } else if ($_source instanceof Response) {
                        $content = $_source->getRawContent();
                    } else {
                        $content = null;
                    }
                }
                $_selector = $selector['selector'] ?? '';
                $_type     = $selector['type'] ?? 'xpath';
                $_childs   = $selector['fields'] ?? '';
                $_callback = $selector['callback'] ?? '';
                $_repeated = $selector['repeated'] ?? false;
                // TODO repeated
                if ($_repeated) {
                    $repeated_fields = $this->fetch_repeated_fields($_type, $_selector, $content, $request, $response);
                    foreach ($repeated_fields as &$_f) {
                        if ($_childs) {
                            // 递归提取
                            $_f = $this->fetch_fields($_childs, $_f, $request, $response, true);
                        }
                        if (is_callable($_callback)) {
                            $_f = call_user_func($_callback, $_f, $this, $request, $response);
                        }
                    }
                    $field = $repeated_fields;
                } else {
                    $field = $this->fetch_single_field($_type, $_selector, $content, $request, $response);
                    if ($_childs) {
                        // 递归提取
                        $field = $this->fetch_fields($_childs, $field, $request, $response, true);
                    }
                    if (is_callable($_callback)) {
                        $field = call_user_func($_callback, $field, $this, $request, $response);
                    }
                }
            } else if (is_callable($selector)) {
                /**
                 * 简单回调
                 */
                $field = $this->fetch_single_field_callback($selector, $content, $request, $response);
            } else {
                throw new SpiderException("Unrecognized selector.");
            }
            /**
             * 只有以下情况会调用on_fetch_field
             * 1. 第一层
             * 2. 嵌套的情况下获取嵌套的整个数组
             */
            if (!$recursive ||
                ($recursive && is_array($selector) && isset($selector['fields']))) {
                if ($this->on_fetch_field) {
                    $field = $this->callback('on_fetch_field', $this, $name, $field);
                }
            }
            $result[$name] = $field;
        }
        return $result;
    }

    private function safeExit($exit_code = 0)
    {
        $this->hook('beforeExit', $this, $exit_code);
        $this->info("spider exited with code {$exit_code}", []);
        $this->callback('on_exit', $this, $exit_code);
    }

    private function callback($callback, ...$params)
    {
        $ret  = null;
        $lock = md5($callback);
        LockManager::getLock($lock);
        if (is_callable($this->$callback)) {
            $ret = call_user_func($this->$callback, ...$params);
        }
        LockManager::releaseLock($lock);
        return $ret;
    }
    private function hook($hook, ...$params)
    {
        $lock = md5($hook);
        LockManager::getLock($hook);
        $this->triggerHook($hook, $params);
        LockManager::releaseLock($hook);
    }

    private function error_handler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext = [])
    {
        $readable_error = '';
        switch ($errno) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $readable_error = 'Fatal Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $readable_error = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $readable_error = 'Notice';
                break;
            case E_STRICT:
                $readable_error = 'Strict';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $readable_error = 'Deprecated';
                break;
            default:
                $readable_error = 'Unknown';
                break;
        }
        $this->error("{$readable_error}: {$errstr} in {$errfile}:{$errline}", []);
        $running = $this->callback('on_error', $this, $errno, $errstr, $errfile, $errline, $errcontext);
        if ($running !== true) {
            $this->safeExit(500);
        }
    }
    private function exception_handler($ex)
    {
        $this->critical((string) $ex, []);
        $running = $this->callback('on_exception', $this, $ex);
        if ($running !== true) {
            $this->safeExit(500);
        }
    }
}
