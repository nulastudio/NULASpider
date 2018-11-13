<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use nulastudio\Networking\Http\UserAgent;

class RandomUserAgent implements PluginContract
{
    public static function install($application, array $userUAs = [])
    {
        $application->hooks['beforeRequest'] += [
            function ($spider, $request) use ($userUAs) {
                $request->setHeader('User-Agent', $userUAs ? array_rand($userUAs) : UserAgent::random());
            },
        ];
    }
}
