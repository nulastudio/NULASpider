<?php

namespace nulastudio\Spider\ServiceProviders;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Kernel;
use nulastudio\Spider\Services\PluginService;

class PluginServiceProvider implements ServiceProviderContract
{
    public function register(Kernel $kernel)
    {
        $kernel->bind('use', function ($application, $plugins, ...$params) {
            return PluginService::install($application, $plugins, ...$params);
        });
    }
}
