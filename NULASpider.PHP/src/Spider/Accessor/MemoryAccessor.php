<?php

namespace nulastudio\Spider\Accessor;

use nulastudio\Spider\Accessor\AccessorInterface;
use nulastudio\Spider\Accessor\AccessorItemNotFoundException;

class MemoryAccessor implements AccessorInterface, \ArrayAccess, \Countable
{
    protected $items = [];

    public function __construct($items = [])
    {
        if (is_array($items)) {
            foreach ($items as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    public function get($index)
    {
        if (!$this->has($index)) {
            throw new AccessorItemNotFoundException("Item {$index} Not Found In The Accessor.");
        }
        return $this->items[$index];
    }
    public function set($index, $value)
    {
        $this->items[$index] = $value;
    }
    public function has($index)
    {
        return array_key_exists($index, $this->items);
    }

    public function all()
    {
        return $this->items;
    }

    public function offsetGet($index)
    {
        return $this->get($index);
    }
    public function offsetSet($index, $value)
    {
        $this->set($index, $value);
    }
    public function offsetUnset($index)
    {
        unset($this->items[$index]);
    }
    public function offsetExists($index)
    {
        return $this->has($index);
    }

    public function count()
    {
        return count($this->items);
    }
}
