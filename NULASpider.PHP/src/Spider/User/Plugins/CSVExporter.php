<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\CSVExporter as _CSVExporter;

class CSVExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('csv', _CSVExporter::class);
    }
}
