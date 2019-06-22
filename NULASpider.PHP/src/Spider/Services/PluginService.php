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
                if (class_exists($plugin)) {
                    $plugin::install($application);
                } else {
                    // FIXME: 使用异常代替错误
                    throw new \Exception("Plugin {$plugin} does not exists.");
                }
            }
        } else {
            if (class_exists($plugins)) {
                $plugins::install($application, ...$params);
            } else {
                // FIXME: 使用异常代替错误
                throw new \Exception("Plugin {$plugins} does not exists.");
            }
        }
        return $application;
    }
}
