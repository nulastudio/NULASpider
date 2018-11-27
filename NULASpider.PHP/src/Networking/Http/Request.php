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
        $this->header = static::$defaultHeader;
        $this->option = static::$defaultOption;
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
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }
    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }
    public function setContentType(string $contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function getOption()
    {
        return $this->option;
    }
    public function setOption(RequestOption $option)
    {
        $this->option = $option;
        return $this;
    }

    public function getHeader(string $header)
    {
        return $this->header->getHeader($header);
    }
    public function getHeaderLine(string $header)
    {
        return $this->header->getHeaderLine($header);
    }
    public function getHeaders($headers)
    {
        return $this->header->getHeaders($headers);
    }
    public function getAllHeaders()
    {
        return $this->header->getAllHeaders();
    }
    public function setHeader($header, $value)
    {
        $this->header->setHeader($header, $value);
        return $this;
    }
    public function setHeaders(array $headers)
    {
        $this->header->setHeaders($headers);
        return $this;
    }
    public function addHeader($header, $value)
    {
        $this->header->addHeader($header, $value);
        return $this;
    }

    public function removeHeader($header)
    {
        $this->header->removeHeader($header);
        return $this;
    }
    public function removeHeaders(array $headers)
    {
        $this->header->removeHeaders($headers);
        return $this;
    }
    public function removeAllHeaders()
    {
        $this->header->removeAllHeaders();
        return $this;
    }
}
