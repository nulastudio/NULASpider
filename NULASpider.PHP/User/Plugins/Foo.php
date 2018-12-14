<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;

class Foo implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->bind('foo', function ($application, ...$params) {
            echo "this is my first plugin.\n";
            var_dump($params);
        });
    }
}
