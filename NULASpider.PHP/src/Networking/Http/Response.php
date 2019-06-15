<?php

namespace nulastudio\Networking\Http;

use nulastudio\Networking\Http\Header;
use nulastudio\Networking\Http\Request;

class Response
{
    public $request;

    public $statusCode;
    public $rawHeader;
    public $rawContent;
    public $parsedHeader;
    public $parsedContent;

    public static function parseResponseString(string $responseStr, Request $request = null)
    {
        // TODO: 从响应字符串创建响应对象
        return null;
    }

    public static function fromSHCResponse(array $SHCResponse, Request $request = null)
    {
        $response                = new static();
        $response->request       = $request;
        $response->statusCode    = $SHCResponse['http_code'] ?? 0;
        $response->rawHeader     = $SHCResponse['header'] ?? '';
        $response->rawContent    = $SHCResponse['data'] ?? '';
        $response->parsedHeader  = Header::parseHeaderString($response->rawHeader);
        $response->parsedContent = $response->rawContent;

        return $response;
    }

    public function getRequest()
    {
        return $this->request;
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    public function getRawHeader()
    {
        return $this->rawHeader;
    }
    public function getRawContent()
    {
        return $this->rawContent;
    }
    public function getParsedHeader()
    {
        return $this->parsedHeader;
    }
    public function getParsedContent()
    {
        return $this->parsedContent;
    }
}
