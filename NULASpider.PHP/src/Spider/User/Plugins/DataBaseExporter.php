<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\DataBaseExporter as _DataBaseExporter;

class DataBaseExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('database', _DataBaseExporter::class);
        $application->hooks['beforeExit'][] = function ($spider, $exit_code) {
            $exporter = $spider->getExporter();
            if ($exporter !== null && $exporter instanceof _DataBaseExporter) {
                $exporter->close();
            }
        };
    }
}
