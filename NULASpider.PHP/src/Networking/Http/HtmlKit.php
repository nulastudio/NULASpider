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
}
