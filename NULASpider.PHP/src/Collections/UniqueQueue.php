<?php

namespace nulastudio\Collections;

use nulastudio\Collections\MemoryQueue;

class UniqueQueue extends MemoryQueue
{
    public function push($value)
    {
        if (!$this->exists($value)) {
            return parent::push($value);
        }
        return false;
    }
}
