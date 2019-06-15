<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\JsonExporter as _JsonExporter;

class JsonExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('json', _JsonExporter::class);
        $application->hooks['beforeExit'][] = function ($spider, $exit_code) {
            $exporter = $spider->getExporter();
            if ($exporter !== null && $exporter instanceof _JsonExporter) {
                $exporter->close();
            }
        };
    }
}
