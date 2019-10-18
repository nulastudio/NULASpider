<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use nulastudio\Spider\Exceptions\PluginException;

class ProxyPool implements PluginContract
{
    private static $proxyPool;

    public static function install($application, ...$params)
    {
        @list($proxies, $enable, $intelligent, $CD) = $params;

        if (!$proxies || !is_array($proxies) || empty($proxies)) {
            throw new PluginException('You must provide some proxies.');
        }

        $pArr[] = $proxies;
        if ($enable !== null) {
            $pArr[] = (bool) $enable;
            if ($intelligent !== null) {
                $pArr[] = (bool) $intelligent;
                if ($CD !== null) {
                    $pArr[] = (int) $CD;
                }
            }
        }

        $proxyPool = new class(...$pArr)
        {
            private $enable      = false;
            private $intelligent = true;
            private $CD          = -1;
            private $proxies     = [];
            private $used        = [];

            public function __construct(array $proxies, bool $enable = false, bool $intelligent = true, $CD = -1)
            {
                $this->proxies     = $proxies;
                $this->enable      = $enable;
                $this->intelligent = $intelligent;
                $this->CD          = $CD;
            }

            public function enable(...$args)
            {
                // 不传参获取，传参设置
                if (empty($args)) {
                    return $this->enable;
                }
                $this->enable = (bool) $args[0];
                if (count($args) > 1) {
                    $this->intelligent = (bool) $args[1];
                }
                return $this->enable;
            }
            public function randomOne()
            {
                $key = mt_rand(0, count($this->proxies ?: $this->used) - 1);
                if ($this->proxies) {
                    return $this->proxies[$key];
                } else {
                    return $this->used[$key]['proxy'];
                }
            }
            public function bestOne()
            {
                if (!$this->intelligent) {
                    return $this->randomOne();
                }
                /**
                 * 随机规则
                 * 1. 优先使用没使用过的
                 * 2. 再者优先 CD 冷却完的
                 * 3. 再无合适的则根据 CD 时间划分权重随机使用
                 */
                if (count($this->proxies) > 0) {
                    $key   = mt_rand(0, count($this->proxies) - 1);
                    $proxy = $this->proxies[$key];
                    unset($this->proxies[$key]);
                    $this->used[] = [
                        'proxy' => $proxy,
                        'time'  => microtime(true),
                    ];
                    return $proxy;
                } else {
                    $CD      = $this->CD;
                    $proxies = array_filter($this->proxies, function ($v, $k) use ($CD) {
                        $proxy = $v;
                        return microtime(true) >= $proxy['time'] + $CD;
                    }, ARRAY_FILTER_USE_BOTH);
                    if (empty($proxies)) {
                        return $this->randomOne();
                    }
                    $key                      = mt_rand(0, count($proxies) - 1);
                    $proxy                    = $proxies[$key];
                    $this->used[$key]['time'] = microtime(true);
                    return $proxy['proxy'];
                }
                // return $this->randomOne();
            }
            public function resetCD()
            {
                $proxies = array_map(function ($proxy) {
                    return $proxy['proxy'];
                }, $this->used);
                $this->used    = [];
                $this->proxies = array_merge($this->proxies, $proxies);
            }
        };
        self::$proxyPool = &$proxyPool;

        $application->hooks['beforeRequest'][] = function ($spider, $request) use ($proxyPool) {
            if ($proxyPool->enable()) {
                $request->getOption()->proxy = $proxyPool->bestOne() ?? '';
            }
        };
        $application->bind('enableProxyPool', function ($spider, ...$params) use ($proxyPool) {
            $enable      = true;
            $intelligent = null;

            if (count($params) >= 1) {
                $enable = (bool) $params[0];
                if (count($params) > 1) {
                    $intelligent = (bool) $params[1];
                }
            }

            if ($intelligent === null) {
                $proxyPool->enable($enable);
            } else {
                $proxyPool->enable($enable, $intelligent);
            }
        });
        $application->bind('resetCD', function ($spider, ...$params) use ($proxyPool) {
            $proxyPool->resetCD();
        });
    }
}
