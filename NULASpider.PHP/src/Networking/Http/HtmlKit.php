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
            'schema'   => '',
            'username' => '',
            'password' => '',
            'host'     => '',
            'port'     => 0,
        ];

        // TODO

        return $proxyPart;
    }
}
