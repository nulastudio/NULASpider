<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;
use User\Exporters\WriteToFileExporter as WTFExporter;

class WriteToFileExporter implements PluginContract
{
    public static function install($application, ...$params)
    {
        // 注册到 file 类型的导出器
        $application->registerExporter('file', WTFExporter::class);
        // 此外，如果导出器需要额外的资源释放的话，可以注册 beforeExit 钩子，用于释放。
        // 需要自己额外的在导出器添加释放逻辑。
        $application->hooks['beforeExit'][] = function ($spider, $exit_code) {
            $exporter = $spider->getExporter();
            if ($exporter !== null && $exporter instanceof WTFExporter) {
                // $exporter->dispose();
            }
        };
    }
}
