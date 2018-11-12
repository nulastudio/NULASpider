<?php

namespace nulastudio\Networking\Http;

class RequestOption
{
    public $timeout        = 0;
    public $autoGzip       = false;
    public $proxy          = '';
    public $autoReferer    = true;
    public $followLocation = true;
    public $maxRedirs      = 0;
    public $httpVersion    = '1.1';

    public function defaultRequestOption()
    {
        return new static();
    }
}
