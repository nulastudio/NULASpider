<?php

namespace nulastudio\Collections;

use nulastudio\Collections\QueueException;
use nulastudio\Collections\QueueInterface;

class MemoryQueue implements QueueInterface
{
    private $queue;
    private $pointer;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
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
        throw new QueueException('The Queue is empty.');
    }
    public function push($value)
    {
        $this->queue[] = $value;
    }
    public function exists($value)
    {
        return isset(array_flip($this->queue)[$value]);
    }
    public function peek()
    {
        if ($this->count()) {
            return $this->queue[$this->pointer + 1];
        }
        throw new QueueException('The Queue is empty.');
    }
    public function count()
    {
        return count($this->queue);
    }
    public function empty()
    {
        $this->init();
    }
    /**
     * 重建索引
     */
    public function reindex()
    {
        $this->queue   = array_values($this->queue);
        $this->pointer = -1;
    }
}
