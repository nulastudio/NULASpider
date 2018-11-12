<?php

namespace nulastudio\Queue;

use \Exception;

class QueueEmptyException extends Exception
{
    public function __construct()
    {
        parent::__construct('Queue is empty');
    }
}
