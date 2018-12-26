<?php

namespace nulastudio\Util;

/**
 * 将可能可执行的内容转换成is_callable可识别的callable
 * 可识别内容:
 * 任何is_callable()===true的callable
 * 任意存在且含有__invoke方法的AnyClass::class
 *
 * @param  mixed    $callable     可能可执行的内容
 * @param  bool     $return_false 当发生无法转换时，是否返回false，默认返回一个空匿名函数
 * @return callable | false
 */
function resolveCallable($callable, bool $return_false = false)
{
    if (is_callable($callable)) {
        return $callable;
    } elseif (is_string($callable) && class_exists($callable)) {
        return resolveCallable(new $callable, $return_false);
    } elseif (is_array($callable) && @$callable[1] === '__invoke') {
        return resolveCallable(@$callable[0], $return_false);
    } else {
        return $return_false ? false : function () {};
    }
}

/**
 * [checkConfig description]
 * @param  array $config         [description]
 * @param  array $standard       [description]
 * @return array [description]
 */
function checkConfig(array $config, array $standard)
{
    return $config + $standard;
}

/**
 * get value of anything
 * @param  mixed   $mixed
 * @return mixed
 */
function value($mixed)
{
    return $mixed;
}

/**
 * [isUseTrait description]
 * @param  [type]    $obj   [description]
 * @param  string    $trait [description]
 * @return boolean
 */
function isUseTrait($obj, string $trait)
{
    $traits = [];
    if (is_object($obj)) {
        $class_name = get_class($obj);
    } else {
        if (is_string($obj) && class_exists($obj)) {
            $class_name = $obj;
        } else {
            return false;
        }
    }
    foreach ([$class_name => $class_name] + (array) class_parents($class_name) as $class) {
        $traits += class_uses($class);
    }
    return isset($traits[$trait]);
}

/**
 * 快速判断数组中是否存在某个值
 */
function inArray($item, $array)
{
    return isset(array_flip($array)[$item]);
}

/**
 * 判断数组是否为索引数组
 */
function isIndexedArray($arr)
{
    if (is_array($arr)) {
        return count(array_filter(array_keys($arr), 'is_string')) === 0;
    }
    return false;
}

/**
 * 判断数组是否为连续的索引数组
 * 以下这种索引数组为非连续索引数组
 * [
 *   0 => 'a',
 *   2 => 'b',
 *   3 => 'c',
 *   5 => 'd',
 * ]
 */
function isContinuousIndexedArray($arr)
{
    if (is_array($arr)) {
        $keys = array_keys($arr);
        return $keys == array_keys($keys);
    }
    return false;
}

/**
 * 判断数组是否为关联数组
 */
function isAssocArray($arr)
{
    if (is_array($arr)) {
        // return !is_indexed_array($arr);
        return count(array_filter(array_keys($arr), 'is_string')) === count($arr);
    }
    return false;
}

/**
 * 判断数组是否为混合数组
 */
function isMixedArray($arr)
{
    if (is_array($arr)) {
        $count = count(array_filter(array_keys($arr), 'is_string'));
        return $count !== 0 && $count !== count($arr);
    }
    return false;
}

/**
 * css转xpath
 * @param  string                        $css_selector css选择器
 * @return nulastudio\Spider\Dom\Xpath
 */
// function cssToXpath(string $css_selector)
// {
//     return Xpath::fromCss($css_selector);
// }

function absoluteUrl(string $base, string $url)
{
    return \phpUri::parse($base)->join($url);
}

function isRegex($pattern)
{
    if (!is_string($pattern)) {
        return false;
    }
    return preg_match('/^[^\da-zA-Z\s].*[^\da-zA-Z\s][a-zA-Z]*$/', $pattern) === 1;
}

/**
 * 获取纯粹的xpath节点
 */
function pureXpath($selector)
{
    $parts     = explode('/', $selector);
    $last_part = $parts[count($parts) - 1];
    if ($last_part{0} === '@') {
        array_pop($parts);
    } else if ($last_part === 'text()') {
        array_pop($parts);
    }
    return implode('/', $parts);
}

function removeHtmlEntities($content)
{
    $no_named_entities   = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
    $no_numeric_entities = preg_replace_callback("/(&#[0-9]+;)/", function ($match) {
        return mb_convert_encoding($match[1], "UTF-8", "HTML-ENTITIES");
    }, $no_named_entities);
    return $no_numeric_entities;
}
