<?php

namespace nulastudio\Networking\Http;

use nulastudio\Networking\Http\Header;
use nulastudio\Networking\Http\RequestOption;

class Request
{
    const REQUEST_METHOD_GET     = 'GET';
    const REQUEST_METHOD_POST    = 'POST';
    const REQUEST_METHOD_HEAD    = 'HEAD';
    const REQUEST_METHOD_OPTIONS = 'OPTIONS';
    const REQUEST_METHOD_PUT     = 'PUT';
    const REQUEST_METHOD_DELETE  = 'DELETE';
    const REQUEST_METHOD_TRACE   = 'TRACE';
    const REQUEST_METHOD_CONNECT = 'CONNECT';

    const REQUEST_CONTENT_TYPE_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    const REQUEST_CONTENT_TYPE_FORM_DATA             = 'multipart/form-data';
    const REQUEST_CONTENT_TYPE_PLAIN_TEXT            = 'text/plain';
    const REQUEST_CONTENT_TYPE_JSON                  = 'application/json';
    const REQUEST_CONTENT_TYPE_JAVASCRIPT            = 'application/javascript';
    const REQUEST_CONTENT_TYPE_XML                   = 'application/xml';
    const REQUEST_CONTENT_TYPE_HTML                  = 'text/html';

    protected static $defaultHeader = null;
    protected static $defaultOption = null;

    // protected $httpVersion = '1.1';
    protected $method      = '';
    protected $url         = '';
    protected $header      = null;
    protected $data        = null;
    protected $contentType = '';
    protected $encoding    = '';

    protected $option = null;

    public function __construct($method, $url, $header = [], $data = null, $contentType = self::REQUEST_CONTENT_TYPE_X_WWW_FORM_URLENCODED, $encoding = 'utf-8')
    {
        if (is_null(static::$defaultHeader)) {
            static::$defaultHeader = Header::defaultHeader();
        }
        if (is_null(static::$defaultOption)) {
            static::$defaultOption = RequestOption::defaultRequestOption();
        }

        $this->method = $method;
        $this->url    = $url;
        $this->header = Header::defaultHeader();
        $this->option = RequestOption::defaultRequestOption();
        foreach ($header as $key => $value) {
            $this->setHeader($key, $value);
        }
        $this->contentType = $contentType;
        $this->encoding    = $encoding;
    }

    public static function setDefaultHeader(Header $header)
    {
        static::$defaultHeader = $header;
    }
    public static function setDefaultOption(RequestOption $option)
    {
        static::$defaultOption = $option;
    }

    public function getMethod()
    {
        return $this->method;
    }
    public function setMethod($method)
    {
        $this->method = (string) $method;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }
    public function setUrl($url)
    {
        $this->url = (string) $url;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
    public function setData($data)
    {
        $this->data = (string) $data;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }
    public function setContentType($contentType)
    {
        $this->contentType = (string) $contentType;
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }
    public function setEncoding($encoding)
    {
        $this->encoding = (string) $encoding;
        return $this;
    }

    public function getOption()
    {
        return $this->option;
    }
    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function getHeader($header)
    {
        try {
            return $this->header->get($header);
        } catch (\Exception $e) {}
    }
    public function getHeaders($headers)
    {
        $result = [];
        foreach ($headers as $header) {
            $result[$header] = $this->getHeader($header);
        }
        return $result;
    }
    public function getAllHeaders()
    {
        return $this->header->all();
    }
    public function setHeader($header, $value)
    {
        $this->header->set($header, $value);
        return $this;
    }
    public function setHeaders($headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        return $this;
    }

    public function removeHeader($header)
    {
        unset($this->header[$header]);
        return $this;
    }
    public function removeHeaders($headers)
    {
        foreach ($headers as $header) {
            $this->removeHeader($header);
        }
        return $this;
    }
    public function removeAllHeaders()
    {
        $this->removeHeaders(array_keys($this->getAllHeaders()));
        return $this;
    }
}
