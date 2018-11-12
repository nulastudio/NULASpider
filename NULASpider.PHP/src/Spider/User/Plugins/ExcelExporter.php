<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\ExcelExporter as _ExcelExporter;

class ExcelExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        $application->registerExporter('excel', _ExcelExporter::class);
        $application->hooks['beforeExit'] = [function ($spider, $exit_code) use ($exporter) {
            $exporter = $spider->getExporter();
            if ($exporter !== null && $exporter instanceof _ExcelExporter) {
                $exporter->save();
            }
        }];
    }
}
