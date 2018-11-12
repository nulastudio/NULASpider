<?php

namespace nulastudio\Networking\Http;

class Header
{
    protected $headers = [];

    public function __construct($headers = [])
    {
        if ($headers) {
            $this->setHeaders($headers);
        }
    }

    public static function defaultHeader()
    {
        return new static([
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            // 'Accept-Encoding' => 'gzip, deflate, sdch, br',
            'Accept-Language' => 'zh-CN,zh;q=0.8',
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        ]);
    }

    public static function mobileHeader()
    {
        return new static([
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            // 'Accept-Encoding' => 'gzip, deflate, sdch, br',
            'Accept-Language' => 'zh-CN,zh;q=0.8',
            'User-Agent'      => 'Mozilla/5.0 (Linux; Android 4.2.0; Nexus 10 Build/JOP24G) AppleWebKit/537.51.1 (KHTML, like Gecko) Chrome/51.0.2704.79 Mobile Safari/537.36',
        ]);
    }
    public static function parseHeaderString(string $headerStr)
    {
        $instance  = new static();
        $headerStr = str_replace("\r", "\n", $headerStr);
        foreach (explode("\n", $headerStr) as $headerLine) {
            if (!empty($headerLine)) {
                list($header, $value) = explode(':', $headerLine);
                $instance->addHeader(trim($header), trim($value));
            }
        }
        return $instance;
    }

    public function getHeader(string $header)
    {
        if ($headerName = $this->assocHeaderName($header)) {
            return $this->headers[$headerName];
        }
        return null;
    }
    public function getHeaderLine(string $header)
    {
        if ($h = $this->getHeader($header)) {
            return is_array($h) ? implode(', ', $h) : $h;
        }
        return null;
    }
    public function getHeaders(array $headers)
    {
        $res = [];
        foreach ($headers as $header) {
            if ($headerName = $this->assocHeaderName($header)) {
                $res[$headerName] = $this->headers[$headerName];
            } else {
                // TBD
                // noHeader VS setToNull
                $res[$header] = null;
            }
        }
        return $result;
    }
    public function getAllHeaders()
    {
        return $this->headers;
    }
    public function getAllHeaderLines()
    {
        return $this->__toString();
    }
    /**
     * 获取已设置的Header中能与之相匹配的Header（大小写不敏感原因）
     *
     * @param  string  $header  要检查的Header
     * @return ?string          存在返回匹配的Header，否则返回null
     *
     */
    protected function assocHeaderName(string $header)
    {
        $header_keys = array_keys($this->headers);
        if (array_walk($header_keys, function (&$h) {
            $h = strtolower($h);
        })) {
            $ret = array_flip($header_keys)[strtolower($header)] ?? null;
            if ($ret !== null) {
                $ret = $array_keys($this->headers)[$ret];
            }
            return $ret;
        }
        return null;
    }
    public function hasHeader(string $header)
    {
        return $this->assocHeaderName($header) !== null;
    }

    public function setHeader(string $header, $value)
    {
        if (is_string($value) || is_array($value)) {
            if ($headerName = $this->assocHeaderName($header)) {
                $this->headers[$headerName] = $value;
            } else {
                $this->headers[$header] = $value;
            }
        }
        return $this;
    }
    public function addHeader(string $header, $value)
    {
        if (is_string($value) || is_array($value)) {
            if ($headerName = $this->assocHeaderName($header)) {
                if (is_array($this->headers[$headerName])) {
                    if (is_array($value)) {
                        $this->headers[$headerName] = array_merge($this->headers[$headerName], $value);
                    } else {
                        $this->headers[$headerName][] = $value;
                    }
                } else {
                    if (is_array($value)) {
                        $this->headers[$headerName] = array_merge([$this->headers[$headerName]], $value);
                    } else {
                        $this->headers[$headerName] = [$this->headers[$headerName], $value];
                    }
                }
            } else {
                $this->headers[$header] = $value;
            }
        }
        return $this;
    }
    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        return $this;
    }
    public function addHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
        return $this;
    }

    public function removeHeader(string $header)
    {
        if ($headerName = $this->assocHeaderName($header)) {
            unset($this[$headerName]);
        }
        return $this;
    }
    public function removeHeaders(array $headers)
    {
        foreach ($headers as $header) {
            $this->removeHeader($header);
        }
        return $this;
    }
    public function removeAllHeaders()
    {
        $this->headers = [];
    }

    public function __toString()
    {
        $headers = '';
        foreach ($this->headers as $key => $value) {
            if (is_array($value)) {
                foreach ($$value as $v) {
                    $headers .= "{$key}: {$v}\r\n";
                }
            } else {
                $headers .= "{$key}: {$value}\r\n";
            }
        }
        return rtrim($headers);
    }
}
