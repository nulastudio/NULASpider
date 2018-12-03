<?php

namespace User\Plugins;

use nulastudio\Networking\Rpc\Aria2 as Aria2Server;
use nulastudio\Spider\Contracts\PluginContract;
use nulastudio\Spider\Exceptions\PluginException;

class Aria2 implements PluginContract
{
    public static function install($application, ...$params)
    {
        @list($url, $token, $savePath) = $params;
        if (!$url) {
            throw new PluginException(self::class . ' required at least one argument. The url must be set.');
        }
        $application->bind('aria2', new Aria2Server($url, $token, $savePath ?? ''));
    }
}
