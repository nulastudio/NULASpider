<?php

namespace nulastudio\Spider\Accessor;

interface AccessorInterface
{
    public function get($index);
    public function set($index, $value);
    public function has($index);
}
