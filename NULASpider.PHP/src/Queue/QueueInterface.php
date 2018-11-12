<?php

namespace nulastudio\Queue;

interface QueueInterface
{
    public function pop();
    public function push($value);
    public function peek();
    public function count();
    public function clear();
}
