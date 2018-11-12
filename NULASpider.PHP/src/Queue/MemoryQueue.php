<?php

namespace nulastudio\Queue;

use nulastudio\Queue\QueueEmptyException;
use nulastudio\Queue\QueueInterface;

class MemoryQueue implements QueueInterface
{
    private $queue;
    private $pointer;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->queue   = [];
        $this->pointer = -1;
    }

    public function pop()
    {
        if ($this->count()) {
            $val = $this->queue[++$this->pointer];
            unset($this->queue[$this->pointer]);
            return $val;
        }
        throw new QueueEmptyException();
    }
    public function push($value)
    {
        $this->queue[] = $value;
    }
    public function peek()
    {
        if ($this->count()) {
            return $this->queue[$this->pointer + 1];
        }
        throw new QueueEmptyException();
    }
    public function count()
    {
        return count($this->queue);
    }
    public function clear()
    {
        $this->init();
    }
}
