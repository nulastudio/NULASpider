<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\PrintOutExporter as _PrintOutExporter;

class PrintOutExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('print', _PrintOutExporter::class);
    }
}
