<?php

namespace nulastudio\Networking\Http;

class HtmlKit
{
    /**
     * 移除 HTML 中的字符实体
     *
     * @param  string   $html HTML 内容
     * @return string
     */
    public static function removeHtmlEntities(string $html)
    {
        $no_named_entities   = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
        $no_numeric_entities = preg_replace_callback("/(&#[0-9]+;)/", function ($match) {
            return mb_convert_encoding($match[1], "UTF-8", "HTML-ENTITIES");
        }, $no_named_entities);
        return $no_numeric_entities;
    }

    /**
     * 解析一个代理字符串
     *
     * [<schema>://][<username>[:<password>]@]<host>[:<port>]
     *
     * @param  string  $proxy 代理字符串
     * @return array
     */
    public static function parseProxy(string $proxy)
    {
        $proxyPart = [
            'schema'   => 'http',
            'username' => '',
            'password' => '',
            'host'     => '',
            'port'     => 1080,
        ];

        // trim
        $proxy = preg_replace('#\s+#', '', $proxy);
        $proxy = rtrim($proxy, '/');

        // has schema?
        if (strpos($proxy, '://') !== false) {
            list($proxyPart['schema'], $proxy) = explode('://', $proxy);
        }

        // has auth?
        if (strpos($proxy, '@') !== false) {
            list($proxyAuth, $proxy) = explode('@', $proxy);

            $proxyAuth             = explode(':', $proxyAuth);
            $proxyPart['username'] = $proxyAuth[0];
            if (count($proxyAuth) == 2) {
                $proxyPart['password'] = $proxyAuth[1];
            }
        }

        $proxy             = explode(':', $proxy);
        $proxyPart['host'] = $proxy[0];
        if (count($proxy) == 2) {
            $proxyPart['port'] = (int) $proxy[1];
        }

        return $proxyPart;
    }

    /**
     * 相对 URL 转绝对 URL
     *
     * @param  string   $base Base URL
     * @param  string   $url  URL
     * @return string
     */
    public static function absoluteUrl(string $base, string $url)
    {
        return \phpUri::parse($base)->join($url);
    }

    /**
     * 将 xpath 表达式拆分为 node 以及 action
     *
     * 原因是某些库在匹配 xpath 时必须提供纯粹的节点表达式，否则无法匹配出来
     *
     * @param  string     $xpath xpath 表达式
     * @return string[]
     */
    public static function xpathNode(string $xpath)
    {
        $parts = [
            'node'   => $xpath,
            'action' => '',
        ];

        $segments = explode('/', $xpath);
        if (count($segments)) {
            $last = $segments[count($segments) - 1];
            if ($last{0} === '@') {
                // @attr
                $parts['action'] = array_pop($segments);
            } else if ($last === 'text()') {
                // text()
                $parts['action'] = array_pop($segments);
            }
            $parts['node'] = implode('/', $segments);
        }

        return $parts;
    }

    /**
     * 将 css 表达式拆分为 node 以及 action
     *
     * 原因是某些库在匹配 css 时必须提供纯粹的节点表达式，否则无法匹配出来
     * 事实上 css 选择器不存在 action 的说法，因为 css 选择器是节点级别的
     * 为了能使 css 选择器选取属性，故扩展出类似 xpath 的语法
     *
     * 目前支持的扩展语法：@attr, @innerHTML, @outerHTML, @innerText
     *
     * @param  string     $css css 表达式
     * @return string[]
     */
    public static function cssNode(string $css)
    {
        $parts = [
            'node'   => $css,
            'action' => '',
        ];

        $css = trim($css);

        $isFound = false;

        $parts['node'] = trim(preg_replace_callback('/@[\w]+$/', function ($matches) use (&$parts, &$isFound) {
            $parts['action'] = trim($matches[0]);
            $isFound         = true;
            return '';
        }, $css));

        if ($isFound) {
            return $parts;
        }

        return $parts;
    }
}
