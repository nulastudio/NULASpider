<?php

namespace nulastudio\Spider\ServiceProviders;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Kernel;
use nulastudio\Spider\Services\ExporterService;

class ExporterServiceProvider implements ServiceProviderContract
{
    private $exporterService;

    public function register(Kernel $kernel)
    {
        $this->exporterService               = new ExporterService($kernel->getApplication()->configs['export'] ?? []);
        $kernel->getApplication()->on_export = function ($spider, $config, $data, $request, $response) {
            // 没有配置导出器就溜了吧
            if (!$config) {
                return;
            }
            $exporter = $this->exporterService->getExporter();
            if ($exporter) {
                $data = is_array($data) ? $data : [$data];
                array_walk($data, function (&$val) use ($exporter) {
                    // 对于其他类型都能友好的转换为字符串
                    // 布尔型转换为大写的FALSE TRUE
                    // 浮点型转换为小数形式（小数点后为0会省略）
                    // 整数型转换为整数
                    if (is_array($val) || is_object($val) || is_resource($val)) {
                        $val = $exporter->handleUnsupportedData($val);
                    }
                });
                $exporter->export($data);
            } else {
                // trigger_error("Can not find a suitable exporter for type {$config['type']}.", E_USER_WARNING);
                // FIXME: 使用Exception代替，set_error_handler以及trigger_error未实现
                throw new \Exception("Can not find a suitable exporter for type {$config['type']}.");
            }
        };

        $kernel->bind('registerExporter', function ($application, string $exporterName, string $exporter, ...$args) {
            return $this->exporterService->register($exporterName, $exporter);
        });
        $kernel->bind('getExporter', function ($application, ...$params) {
            return $this->exporterService->getExporter();
        });
    }
}
