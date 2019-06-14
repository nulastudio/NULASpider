<?php

namespace nulastudio\Collections;

use nulastudio\Collections\QueueInterface;

class MemoryQueue implements QueueInterface
{
    private $queue;
    private $pointer;
    private $popCount;
    private $maxCount = 500000;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        $this->queue    = [];
        $this->pointer  = -1;
        $this->popCount = 0;
    }

    public function pop()
    {
        if ($this->count()) {
            $val = $this->queue[++$this->pointer];
            unset($this->queue[$this->pointer]);
            if (++$this->popCount >= $this->maxCount) {
                $this->reindex();
            }
            return $val;
        }
        return null;
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
        return null;
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
        $this->queue    = array_values($this->queue);
        $this->pointer  = -1;
        $this->popCount = 0;
    }
}
