<?php

namespace nulastudio\Spider\Services;

use nulastudio\Spider\Contracts\PluginContract;
use nulastudio\Spider\Exceptions\PluginException;
use nulastudio\Spider\Services\BaseService;

class PluginService extends BaseService
{
    public static function install($application, $plugins, ...$params)
    {
        if (is_array($plugins)) {
            foreach ($plugins as $plugin) {
                $plugin::install($application);
            }
        } else {
            $plugins::install($application, ...$params);
        }
        return $application;
    }
}
