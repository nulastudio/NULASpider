<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\PDOExporter as _PDOExporter;

class PDOExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('pdo', _PDOExporter::class);
        $application->hooks['beforeExit'][] = function ($spider, $exit_code) {
            $exporter = $spider->getExporter();
            if ($exporter !== null && $exporter instanceof _PDOExporter) {
                $exporter->close();
            }
        };
    }
}
