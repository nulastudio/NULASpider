<?php

namespace User\Plugins;

use nulastudio\Networking\Http\UserAgent;
use nulastudio\Spider\Contracts\PluginContract;

class RandomUserAgent implements PluginContract
{
    public static function install($application, array $userUAs = [])
    {
        $application->hooks['beforeRequest'][] = function ($spider, $request) use ($userUAs) {
            $request->setHeader('User-Agent', $userUAs ? array_rand($userUAs) : UserAgent::random());
        };
    }
}
