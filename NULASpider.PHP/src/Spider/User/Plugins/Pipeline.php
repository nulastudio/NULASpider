<?php

namespace User\Plugins;

use nulastudio\Middleware;
use nulastudio\Spider\Contracts\PluginContract;

class Pipeline implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->bind('pipeline', new Middleware);
    }
}
