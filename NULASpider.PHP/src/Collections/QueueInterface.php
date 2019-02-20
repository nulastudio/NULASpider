<?php

namespace nulastudio\Collections;

interface QueueInterface extends \Countable
{
    public function pop();
    public function push($value);
    public function exists($value);
    public function peek();
    public function count();
    public function empty();
}
