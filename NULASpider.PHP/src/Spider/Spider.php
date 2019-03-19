<?php

namespace nulastudio\Spider;

use liesauer\SimpleHttpClient;
use nulastudio\Collections\ConcurrentMemoryQueue as ConcurrentQueue;
use nulastudio\Collections\UniqueQueue;
use nulastudio\Encoding\Encoding;
use nulastudio\Log\NullLogger;
use nulastudio\Networking\Http\Request;
use nulastudio\Networking\Http\Response;
use nulastudio\Spider\Application;
use nulastudio\Spider\Exceptions\SpiderException;
use nulastudio\Spider\ServiceProviders\ExporterServiceProvider;
use nulastudio\Spider\ServiceProviders\HookServiceProvider;
use nulastudio\Spider\ServiceProviders\PluginServiceProvider;
use nulastudio\Threading\LockManager;
use nulastudio\Util;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

// use Sabre\Uri;

class Spider
{
    // URL 队列（UniqueQueue）
    private $urlQueue;
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
        'error'      => 0,
        'exception'  => 0,
    ];

    // 钩子挂接点
    private $hook_points = [
        'beforeRequest',
        'beforeExit',
    ];

    // 回调函数以及功能覆写
    private $callbacks = [
        'on_start'         => null,
        'on_exit'          => null,
        'on_error'         => null,
        'on_exception'     => null,
        'on_request'       => null,
        'on_status_code'   => null,
        'on_process'       => null,
        'on_scan_url'      => null,
        'on_list_url'      => null,
        'on_content_url'   => null,
        'on_fetch_field'   => null,
        'on_fetch_page'    => null,
        'on_export'        => null,
        'requestOverride'  => null,
        'findUrlsOverride' => null,
        'filterUrls'       => null,
        'encodingHandler'  => null,
    ];

    // 爬虫配置项
    private $configs = [];

    // logger
    use LoggerTrait;
    use LoggerAwareTrait;

    public function __construct(array $configs = [])
    {
        try {
            // 初始化URL队列、请求队列、处理队列
            $this->urlQueue      = new UniqueQueue();
            $this->downloadQueue = new ConcurrentQueue();
            $this->processQueue  = new ConcurrentQueue();

            // 接收并检查配置
            $this->checkConfig($configs);

            $this->setLogger($this->configs['logger']);

            // 注册内核
            $this->kernel = new Kernel($this, [
                new HookServiceProvider($this->hook_points),
                PluginServiceProvider::class,
                ExporterServiceProvider::class,
            ]);
            $this->kernel->bootstrap();
        } catch (\Exception $e) {
            exit("Exception occurred while booting.\n" . (string) $e);
        }

        set_error_handler([$this, 'errorHandler'], error_reporting());
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function __get($prop)
    {
        // readonly
        switch ($prop) {
            case 'configs':
                return $this->configs;
            case 'monitor':
                return $this->monitor;
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
            'UI'                  => true,
            'input_encoding'      => 'smart',
            'fallback_encoding'   => '',
            'output_encoding'     => 'auto',
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

        $this->callback('on_start', $this);

        if ($this->initWorker()) {
            Encoding::registerProvider();
            Application::run($this);
        }

        // 安全退出
        $this->safeExit();
    }

    private function initWorker()
    {
        if (!$this->configs['scan_urls']) {
            return false;
        }
        foreach ($this->configs['scan_urls'] as $scan_url) {
            if (is_string($scan_url) && !Util\isRegex($scan_url) && strpos($scan_url, 'http') === 0) {
                $this->addUrl($scan_url);
            }
        }
        return true;
    }

    public function addUrl($url, $prevUrl = null)
    {
        try {
            LockManager::getLock('add_url');
            $url_hash = md5($url);
            if (!$this->urlQueue->exists($url_hash)) {
                $this->urlQueue->push($url_hash);
                $request = new Request(Request::REQUEST_METHOD_GET, $url);
                if ($prevUrl) {
                    $request->setHeader('Referer', $prevUrl);
                }

                $this->downloadQueue->push($request);
            }
        } finally {
            LockManager::releaseLock('add_url');
        }
    }

    public function getUrl()
    {
        if ($this->hasUrl()) {
            return $this->downloadQueue->pop();
        }
        return null;
    }
    public function hasUrl()
    {
        return $this->downloadQueue->count() !== 0;
    }

    public function getResponse()
    {
        if ($this->hasResponse()) {
            return $this->processQueue->pop();
        }
        return null;
    }
    public function hasResponse()
    {
        return $this->processQueue->count() !== 0;
    }

    public function fetchUrl($request)
    {
        // LockManager::getLock("fetchUrl");
        $this->hook('beforeRequest', $this, $request);

        $response = null;
        if ($this->hasCallback('requestOverride')) {
            $response = $this->callback('requestOverride', $this, $request);
            if (!$response instanceof Response) {
                return;
            }
        } else {
            $method = $request->getMethod();
            $url    = $request->getUrl();
            $header = $request->getAllHeaders();
            $cookie = '';
            $data   = $request->getData();
            $option = $request->getOption();
            // bug
            /*
            $uri = preg_replace_callback(
            '/[^[:ascii:]]/u',
            function($matches) {
            return rawurlencode($matches[0]);
            },
            $uri
            );
             */
            // $proxy     = Uri\parse($option->proxy);

            /**
             * CURLOPT_SSL_VERIFY* always enabled
             */

            $proxy     = parse_url($option->proxy) ?: [];
            $curl_opts = [
                // only 1.0/1.1 supported now
                CURLOPT_HTTP_VERSION   => @[
                    ''    => CURL_HTTP_VERSION_NONE,
                    '1'   => CURL_HTTP_VERSION_1_0,
                    '1.0' => CURL_HTTP_VERSION_1_0,
                    '1.1' => CURL_HTTP_VERSION_1_1,
                    '2'   => CURL_HTTP_VERSION_2,
                ][$option->httpVersion] ?? '',
                CURLOPT_TIMEOUT        => $option->timeout,
                CURLOPT_FOLLOWLOCATION => $option->followLocation,
                // CURLOPT_AUTOREFERER    => $option->autoReferer,
                CURLOPT_MAXREDIRS      => $option->maxRedirs,
            ];
            if ($proxy && $proxy['scheme'] && $proxy['host'] && $proxy['port']) {
                $scheme    = strtolower($proxy['scheme']);
                $host      = $proxy['host'];
                $port      = $proxy['port'];
                $user      = $proxy['user'] ?? '';
                $pass      = $proxy['pass'] ?? '';
                $protocols = [
                    // only http supported now
                    'http' => CURLPROXY_HTTP,
                    // 'socks4'  => CURLPROXY_SOCKS4,
                    // 'socks4a' => CURLPROXY_SOCKS4A,
                    // 'socks5'  => CURLPROXY_SOCKS5,
                    // 'socks5h' => CURLPROXY_SOCKS5_HOSTNAME,
                ];
                if (!isset($protocols[$scheme])) {
                    $this->warning("Unsupported proxy protocol: {$proxy['scheme']}", []);
                } else {
                    $curl_opts[CURLOPT_PROXYTYPE] = $protocols[$scheme];
                    $curl_opts[CURLOPT_PROXY]     = "{$proxy['host']}:{$proxy['port']}";
                    if ($user) {
                        $curl_opts[CURLOPT_PROXYUSERPWD] = "{$proxy['user']}:{$proxy['pass']}";
                    }
                }
            }
            $response = null;
            if ($method === Request::REQUEST_METHOD_GET) {
                $response = SimpleHttpClient::quickGet($url, $header, $cookie, $data, $curl_opts);
            } else if ($method === Request::REQUEST_METHOD_POST) {
                $response = SimpleHttpClient::quickPost($url, $header, $cookie, $data, $curl_opts);
            }
            $response = Response::fromSHCResponse($response, $request);
        }

        if ($this->hasCallback('on_request')) {
            $ret = $this->callback('on_request', $this, $request, $response);
            if ($ret === false) {
                return;
            } else if ($ret instanceof Response) {
                $response = $ret;
            }
        }

        if ($this->hasCallback('on_status_code')) {
            $status_code = $response->getStatusCode();
            $ret         = $this->callback('on_status_code', $this, $status_code, $request, $response);
            if ($ret === false) {
                return;
            } else if ($ret instanceof Response) {
                $response = $ret;
            }
        }

        if ($this->hasCallback('on_process')) {
            $url = $request->getUrl();
            $ret = $this->callback('on_process', $this, $url, $request, $response);
            if ($ret === false) {
                return;
            }
        }

        LockManager::getLock('update_downloaded');
        $this->monitor['downloaded']++;
        LockManager::releaseLock('update_downloaded');
        $this->processQueue->push($response);
        // LockManager::releaseLock("fetchUrl");
    }

    public function processResponse($response)
    {
        // LockManager::getLock("processResponse");

        // 编码转换
        $request           = $response->getRequest();
        $url               = $request->getUrl();
        $content           = $response->getRawContent();
        $input_encoding    = strtoupper($this->configs['input_encoding']);
        $fallback_encoding = strtoupper($this->configs['fallback_encoding']);
        $encoding          = $this->encodingDetect($response, $input_encoding);
        if (!$encoding) {
            $encoding = $this->encodingDetect($response, $fallback_encoding);
            if (!$encoding) {
                #warning should stop the spider?
                $this->log(LogLevel::WARNING, 'Encoding detect failed.');
            }
        }

        if ($encoding) {
            if ($encoding !== 'UTF-8') {
                $content = iconv($encoding, 'UTF-8//TRANSLIT', $content);
            }
        }

        if ($this->isScanUrl($url) && $this->hasCallback('on_scan_url')) {
            $ret = $this->callback('on_scan_url', $this, $url, $request, $response);
            if ($ret !== true) {
                return;
            }
        }
        if ($this->isListUrl($url) && $this->hasCallback('on_list_url')) {
            $ret = $this->callback('on_list_url', $this, $url, $request, $response);
            if ($ret !== true) {
                return;
            }
        }
        if ($this->isContentUrl($url)) {
            if ($this->hasCallback('on_content_url')) {
                $ret = $this->callback('on_content_url', $this, $url, $request, $response);
                if ($ret !== true) {
                    return;
                }
            }
            $result = $this->fetchFields($this->configs['fields'], $content, $request, $response);
            if ($this->hasCallback('on_fetch_page')) {
                $result = $this->callback('on_fetch_page', $this, $result, $request, $response);
                if ($result === false) {
                    return;
                }
            }
            if ($this->hasCallback('on_export')) {
                $this->callback('on_export', $this, $this->configs['export'], $result, $request, $response);
            }
        }
        $this->findListUrl($content, $request, $response);
        $this->findContentUrl($content, $request, $response);

        LockManager::getLock('update_processed');
        $this->monitor['processed']++;
        LockManager::releaseLock('update_processed');
        // LockManager::releaseLock("processResponse");
    }

    private function encodingDetect($response, $processor)
    {
        if (empty($response) || empty($processor)) {
            return false;
        }
        $encoding = 'ISO-8859-1';
        if ($processor === 'AUTO' || $processor === 'SMART') {
            // use built-in detector
            $CBC = $processor === 'SMART';

            // detect from header
            // detect from meta if is html also
            // priority: header > meta
            $contentType = $response->getParsedHeader()->getHeaderLine('Content-Type');
            if ($contentType) {
                // Content-type: MIME类型; charset=编码
                $isHTML = false;
                if (preg_match('/^\s*(?<mime>[\w\/]+);?.*$/i', $contentType, $result) === 1) {
                    $isHTML = strtolower($result['mime']) === 'text/html';
                    if (preg_match('/charset=(?<encoding>[\w\-]*).*$/i', $contentType, $result) === 1) {
                        $encoding = $result['encoding'];
                    }
                    if ($isHTML && empty($encoding)) {
                        // detect from meta
                        /**
                         * HTML4: <meta http-equiv="Content-Type" content="text/html;charset=XXX">
                         * HTML5: <meta charset="XXX">
                         */
                        if (preg_match('/<meta\s*http-equiv="Content-Type"\s*content="[\w\/]*;charset=(?<encoding>[\w\-]*)".*$/i', $contentType, $result) === 1) {
                            $encoding = $result['encoding'];
                        } else if (preg_match('/<meta\s*charset="(?<encoding>[\w\-]*)".*$/i', $contentType, $result) === 1) {
                            $encoding = $result['encoding'];
                        }
                    }
                }
            }

            if ($CBC && !$encoding) {
                $content = $response->getRawContent();
                return Encoding::detect($content);
            }
        } elseif ($processor === 'HANDLER') {
            if ($this->hasCallback('encodingHandler')) {
                return $this->callback('encodingHandler', $this, $response);
            } else {
                $encoding = '';
                $this->log(LogLevel::WARNING, 'encodingHandler does not exists.');
            }
        } else {
            // TODO
            // validate if it is valid encoding
            $encoding = $processor;
        }
        return strtoupper($encoding);
    }

    public function isScanUrl($url)
    {
        return $this->isUrlMatchesPattern($url, $this->configs['scan_urls']) !== false;
    }
    public function isListUrl($url)
    {
        return $this->isUrlMatchesPattern($url, $this->configs['list_url_pattern']) !== false;
    }
    public function isContentUrl($url)
    {
        return $this->isUrlMatchesPattern($url, $this->configs['content_url_pattern']) !== false;
    }
    private function isUrlMatchesPattern($url, $pattern)
    {
        if (empty($url) || empty($pattern) || !is_string($url)) {
            return false;
        }
        $patterns        = is_array($pattern) ? $pattern : [$pattern];
        $matched_pattern = false;
        foreach ($patterns as $patt) {
            if (is_string($patt)) {
                if (Util\isRegex($patt) ? (preg_match($patt, $url) === 1) : ($url === $patt)) {
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

    private function findListUrl($content, $request, $response)
    {
        $prevUrl = $request->getUrl();
        $urls    = [];
        if ($this->hasCallback('findUrlsOverride')) {
            $ret = $this->callback('findUrlsOverride', $this, $content, $request, $response);
            if (is_array($ret)) {
                $urls = $ret;
            }
        } else {
            $urls = $this->findUrls($content, $request, $response);
        }
        if ($this->hasCallback('filterUrls')) {
            $urls = $this->callback('filterUrls', $this, $urls);
            if (!is_array($urls)) {
                $urls = [];
            }
        }
        foreach ($urls as $url) {
            $url = Util\absoluteUrl($prevUrl, $url);
            if ($this->isListUrl($url)) {
                $this->addUrl($url, $prevUrl);
            }
        }
    }

    private function findContentUrl($content, $request, $response)
    {
        $prevUrl = $request->getUrl();
        $urls    = [];
        if ($this->findUrlsOverride) {
            $ret = $this->callback('findUrlsOverride', $this, $content, $request, $response);
            if (is_array($ret)) {
                $urls = $ret;
            }
        } else {
            $urls = $this->findUrls($content, $request, $response);
        }
        if ($this->hasCallback('filterUrls')) {
            $urls = $this->callback('filterUrls', $this, $urls);
            if (!is_array($urls)) {
                $urls = [];
            }
        }
        foreach ($urls as $url) {
            $url = Util\absoluteUrl($prevUrl, $url);
            if ($this->isContentUrl($url)) {
                $this->addUrl($url, $prevUrl);
            }
        }
    }

    private function findUrls($content, $request, $response)
    {
        $urls = [];
        try {
            $document = new \HtmlAgilityPack\HtmlDocument();
            $document->LoadHtml($content);
            if ($document->DocumentNode) {
                $nodes = $document->DocumentNode->SelectNodes("//a[@href]") ?? [];
                foreach ($nodes as $node) {
                    // $urls[] = $node->Attributes["href"]->Value;
                    $val = $node->Attributes->get_Item('href')->Value;
                    if ($val) {
                        $urls[] = Util\removeHtmlEntities($val);
                    }
                }
            }
        } catch (\Exception $e) {}
        return $urls;
    }

    private function fetchSingleField($type, $selector, $content, $request, $response)
    {
        if ($type === 'xpath') {
            return $this->fetchSingleFieldXpath($selector, $content, $request, $response);
        } else if ($type === 'regex') {
            return $this->fetchSingleFieldRegex($selector, $content, $request, $response);
        } else if ($type === 'css') {
            return $this->fetchSingleFieldCss($selector, $content, $request, $response);
        } else if ($type === 'callback') {
            return $this->fetchSingleFieldCallback($selector, $content, $request, $response);
        } else if ($type === 'raw') {
            return $this->fetchSingleFieldRaw($selector, $content, $request, $response);
        }
        throw new SpiderException("Unrecognized selector type: {$type}.");
    }
    private function fetchRepeatedFields($type, $selector, $content, $request, $response)
    {
        if ($type === 'xpath') {
            return $this->fetchRepeatedFieldsXpath($selector, $content, $request, $response);
        } else if ($type === 'regex') {
            return $this->fetchRepeatedFieldsRegex($selector, $content, $request, $response);
        } else if ($type === 'css') {
            return $this->fetchRepeatedFieldsCss($selector, $content, $request, $response);
        } else if ($type === 'callback') {
            return $this->fetchRepeatedFieldsCallback($selector, $content, $request, $response);
        } else if ($type === 'raw') {
            return $this->fetchRepeatedFieldsRaw($selector, $content, $request, $response);
        }
        throw new SpiderException("Unrecognized selector type: {$type}.");
    }
    private function fetchSingleFieldXpath(string $selector, string $content, $request, $response)
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
    private function fetchRepeatedFieldsXpath(string $selector, string $content, $request, $response)
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
    private function fetchSingleFieldRegex(string $selector, string $content, $request, $response)
    {
        if (Util\isRegex($selector)) {
            if (preg_match($selector, $content, $matches) === 1) {
                return $matches[0];
            }
        }
    }
    private function fetchRepeatedFieldsRegex(string $selector, string $content, $request, $response)
    {
        $result = [];
        // 如果$matches数组只有一个元素，则表示无分组，使用匹配到的全文作为结果
        // 如果$matches数组有多个元素，则表示有分组，强制使用第一个分组作为结果
        if (Util\isRegex($selector)) {
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
    private function fetchSingleFieldCss(string $selector, string $content, $request, $response)
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
    private function fetchRepeatedFieldsCss(string $selector, string $content, $request, $response)
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
    private function fetchSingleFieldCallback(callable $callback, string $content, $request, $response)
    {
        return call_user_func($callback, $content, $this, $request, $response);
    }
    private function fetchRepeatedFieldsCallback(callable $callback, string $content, $request, $response)
    {
        $ret = call_user_func($callback, $content, $this, $request, $response);
        // 强制包装成数组，保证结构不被破坏
        if (!is_array($ret)) {
            $ret = [$ret];
        }
        return $ret;
    }
    private function fetchSingleFieldRaw($selector, $content, $request, $response)
    {
        return $content;
    }
    private function fetchRepeatedFieldsRaw($selector, $content, $request, $response)
    {
        $res = $content;
        // 强制包装成数组，保证结构不被破坏
        if (!is_array($res)) {
            $res = [$res];
        }
        return $res;
    }

    private function fetchFields($fields, $content, $request, $response, $recursive = false)
    {
        $result = [];
        foreach ($fields as $name => $selector) {
            $field = null;
            if (is_string($selector)) {
                /**
                 * 简单xpath
                 */
                $field = $this->fetchSingleFieldXpath($selector, $content, $request, $response);
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
                        $content = call_user_func($_source, $content, $this, $request, $response);
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
                        $content = Response::fromSHCResponse($_response, $request)->getRawContent();
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
                    $repeated_fields = $this->fetchRepeatedFields($_type, $_selector, $content, $request, $response);
                    foreach ($repeated_fields as &$_f) {
                        if ($_childs) {
                            // 递归提取
                            $_f = $this->fetchFields($_childs, $_f, $request, $response, true);
                        }
                        if (is_callable($_callback)) {
                            $_f = call_user_func($_callback, $_f, $this, $request, $response);
                        }
                    }
                    $field = $repeated_fields;
                } else {
                    $field = $this->fetchSingleField($_type, $_selector, $content, $request, $response);
                    if ($_childs) {
                        // 递归提取
                        $field = $this->fetchFields($_childs, $field, $request, $response, true);
                    }
                    if (is_callable($_callback)) {
                        $field = call_user_func($_callback, $field, $this, $request, $response);
                    }
                }
            } else if (is_callable($selector)) {
                /**
                 * 简单回调
                 */
                $field = $this->fetchSingleFieldCallback($selector, $content, $request, $response);
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
                if ($this->hasCallback('on_fetch_field')) {
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
        // exit($exit_code);
        // .NET way
        // 调试时不会引起ScriptDieException
        \System\Environment::Exit($exit_code);
    }

    private function validCallback(string $callback)
    {
        $lock = 'validCallback_' . md5($callback);
        LockManager::getLock($lock);
        $ret = Util\resolveCallable($this->$callback, true);
        LockManager::releaseLock($lock);
        return $ret !== false;
    }

    private function hasCallback(string $callback)
    {
        return $this->validCallback($callback);
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
        LockManager::getLock($lock);
        $this->triggerHook($hook, $params);
        LockManager::releaseLock($lock);
    }

    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext = [])
    {
        LockManager::getLock('update_error');
        $this->monitor['error']++;
        LockManager::releaseLock('update_error');
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
    public function exceptionHandler(\Exception $ex)
    {
        LockManager::getLock('update_exception');
        $this->monitor['exception']++;
        LockManager::releaseLock('update_exception');
        $this->critical((string) $ex, []);
        $running = $this->callback('on_exception', $this, $ex);
        if ($running !== true) {
            $this->safeExit(500);
        }
    }
}
