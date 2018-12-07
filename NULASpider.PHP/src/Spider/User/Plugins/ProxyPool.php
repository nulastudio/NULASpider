<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use nulastudio\Spider\Exceptions\PluginException;

class ProxyPool implements PluginContract
{
    public static function install($application, ...$params)
    {
        @list($proxies, $enable) = $params;
        if (!$proxies || !is_array($proxies) || empty($proxies)) {
            throw new PluginException('You must provide some proxies.');
        }

        $proxyPool = new class($proxies, (bool) $enable)
        {
            private $enable  = false;
            private $proxies = [];

            public function __construct(array $proxies, bool $enable = false)
            {
                $this->proxies = $proxies;
                $this->enable  = $enable;
            }

            public function enable($enable = null)
            {
                if ($enable === null) {
                    return $this->enable;
                }
                $this->enable = (bool) $enable;
                return $this->enable;
            }
            public function randomOne()
            {
                return empty($this->proxies) ? null : $this->proxies[array_rand($this->proxies)];
            }
        };

        $application->hooks['beforeRequest'][] = function ($spider, $request) use ($proxyPool) {
            if ($proxyPool->enable()) {
                $request->getOption()->proxy = $proxyPool->randomOne() ?? '';
            }
        };
        $application->bind('enableProxyPool', function ($spider, $enable = true, ...$params) use ($proxyPool) {
            $proxyPool->enable((bool) $enable);
        });
    }
}
