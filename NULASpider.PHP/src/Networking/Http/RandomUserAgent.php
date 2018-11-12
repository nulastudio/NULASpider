<?php

namespace nulastudio\Networking\Http;

use nulastudio\Networking\Http\UserAgent;

class RandomUserAgent extends UserAgent
{
    public function __toString()
    {
        return static::random();
    }
}
