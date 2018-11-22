<?php

namespace nulastudio\Spider\Services;

use nulastudio\Spider\Services\BaseService;

class HookService extends BaseService implements \ArrayAccess
{
    private $operator;
    private $hooks = [];

    public function __construct(array $hooks)
    {
        foreach ($hooks as $hook) {
            if (is_string($hook)) {
                $this->hooks[$hook] = null;
            } else {
                trigger_error("Trying to register a invalid hook point.", E_USER_WARNING);
            }
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_array($value)) {
            $value = array_filter($value, 'is_callable');
        } else if (!is_callable($value)) {
            $this->operator = null;
            return;
        }
        if ($offset === null) {
            // 添加
            if ($this->operator) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                $this->hooks[$this->operator] = array_merge($this->hooks[$this->operator] ?? [], $value);
            }
        } else {
            // 设置
            if (is_array($value)) {
                $this->hooks[$offset] = $value;
            } else {
                $this->hooks[$offset] = [$value];
            }
        }
        $this->operator = null;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->hooks);
    }

    /**
     * 清空钩子挂载点
     */
    public function offsetUnset($offset)
    {
        if (isset($this->hooks[$offset])) {
            $this->hooks[$offset] = null;
        }
    }

    public function offsetGet($offset)
    {
        $this->operator = $offset;
        return $this;
    }

    public function addHook(string $hook, callable $callback)
    {
        $this[$hook][] = $callback;
    }

    public function cleanHook(string $hook = null)
    {
        if ($hook !== null) {
            unset($this[$hook]);
        } else {
            foreach (array_keys($this->hooks) as $key) {
                unset($this[$key]);
            }
        }
    }

    public function getHooks(string $hook = null)
    {
        if ($hook === null) {
            return $this->hooks;
        }
        return isset($this[$hook]) ? ($this->hooks[$hook] ?? []) : null;
    }
}
