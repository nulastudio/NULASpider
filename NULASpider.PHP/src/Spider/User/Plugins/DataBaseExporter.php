<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\DataBaseExporter as _DataBaseExporter;

class DataBaseExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('database', _DataBaseExporter::class);
    }
}
