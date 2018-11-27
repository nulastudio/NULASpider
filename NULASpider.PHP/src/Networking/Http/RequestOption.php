<?php

namespace nulastudio\Networking\Http;

class RequestOption
{
    public $timeout        = 0;
    public $autoGzip       = false;
    public $proxy          = ''; // protocal://username:password@server:port
    public $autoReferer    = true;
    public $followLocation = true;
    public $maxRedirs      = 0;
    public $httpVersion    = '';

    public function defaultRequestOption()
    {
        return new static();
    }
}
