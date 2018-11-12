<?php

namespace nulastudio\Spider\Services;

use nulastudio\Spider\Services\BaseService;
use nulastudio\Spider\Util;

class HookService extends BaseService implements \ArrayAccess
{
    const __TAG__ = 'NULAER ٩(๑òωó๑)۶';

    protected $hooks = [];

    public function __construct(array $hooks)
    {
        // 注册钩子点
        foreach ($hooks as $hook) {
            if (is_string($hook)) {
                $this->hooks[$hook] = null;
            } else {
                $type = gettype($hook);
                trigger_error("Trying to register a invalid hook point. ({$type} type detected).", E_USER_WARNING);
            }
        }
    }

    /**
     *
     * 为了区分开 = 操作符与 += 操作符，在输出的时候多加一个标识
     *
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->hooks)) {
            if ($this->hooks[$prop] === null) {
                return [];
            }
            $hooks = [];
            foreach ($this->hooks[$prop] as $hook) {
                $hooks[md5(uniqid(mt_rand(), true))] = $hook;
            }
            return [self::__TAG__ => md5(self::__TAG__)] + $hooks;
        }
        return null;
    }

    /**
     *
     * 为了区分开 = 操作符与 += 操作符，在使用的时候识别出标识就要去掉
     *
     */
    public function __set($prop, $val)
    {
        if (array_key_exists($prop, $this->hooks)) {
            if ($val === null) {
                $this->hooks[$prop] = [];
            } elseif (is_array($val)) {
                // if (array_key_exists(self::__TAG__, $val) && $val[self::__TAG__] === md5(self::__TAG__)) {
                //     $val = array_slice($val, count($this->hooks[$prop]) + 1);
                // } else {
                //     $this->hooks[$prop] = [];
                // }

                // 清空hooks
                $this->hooks[$prop] = [];

                $hasTag = array_key_exists(self::__TAG__, $val) && $val[self::__TAG__] === md5(self::__TAG__);

                if ($hasTag) {
                    // 去除标志位
                    unset($val[self::__TAG__]);
                }

                foreach ($val as $key => $item) {
                    // 正常来说，带32位字符串键名的都是内部处理过的，不应该再次添加
                    // if ($hasTag && is_string($key) && ($key === self::__TAG__ || strlen($key) == 32)) {
                    //     continue;
                    // }
                    if (($callable = Util\resolveCallable($item, true)) !== false) {
                        $this->hooks[$prop][] = $callable;
                    }
                }
            } elseif (($callable = Util\resolveCallable($val, true)) !== false) {
                $this->hooks[$prop] = [$callable];
            }
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->hooks);
    }

    public function offsetUnset($offset)
    {
        if (isset($this->hooks[$offset])) {
            // 不能使用unset卸载钩子点
            // 卸载钩子点会导致系统不正常运行甚至崩溃
            // 只能置为null清空钩子点
            // unset($this->hooks[$offset]);
            $this->hooks[$offset] = null;
        }
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * 获取所有钩子点或者特定钩子点上的钩子函数
     * @param  string|null $group 钩子点，为null表示取所有钩子点
     * @return array              钩子点数组
     */
    public function getHooks(string $group = null)
    {
        if (empty($group)) {
            return $this->hooks;
        }
        return $this->hooks[$group] ?? [];
    }

}
