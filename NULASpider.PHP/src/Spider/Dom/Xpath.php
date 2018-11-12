<?php

namespace nulastudio\Spider\Dom;

use PhpCss;
use PhpCss\Ast\Visitor\Xpath as XpathVisitor;

class Xpath implements \ArrayAccess
{
    protected $xpath  = '';
    protected $broken = false;

    public function __construct($xpath)
    {
        if ($xpath === null) {
            $this->broken = true;
        } else {
            $this->xpath = (string) $xpath;
        }
    }

    /**
     * 使用css选择器转换成Xpath对象
     */
    public static function fromCss($css)
    {
        // $xpath;
        // try {
        //     $xpath = PhpCss::toXpath($css,
        //         XpathVisitor::OPTION_EXPLICIT_NAMESPACES
        //          |
        //         XpathVisitor::OPTION_USE_DOCUMENT_CONTEXT
        //     );
        // } catch (\Exception $e) {
        //     $xpath = null;
        // }
        // return new self($xpath);
    }

    /**
     * 返回Xpath对象所对应的xpath语句
     */
    public function __toString()
    {
        return $this->xpath;
    }

    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * Xpath[@attr|text()|statement]语法实现
     */
    public function offsetGet($offset)
    {
        if ($this->broken) {
            return '';
        }
        return (string) $this . (empty($offset) ? '' : ('/' . (string) $offset));
    }

    public function offsetSet($offset, $value)
    {}

    public function offsetUnset($offset)
    {}

    /**
     * Xpath[@attr]语法
     */
    public function attr(string $attr)
    {
        return $this["@{$attr}"];
    }

    /**
     * Xpath[text()]语法
     */
    public function text()
    {
        return $this['text()'];
    }

}
