<?php

namespace nulastudio\Spider\ServiceProviders;

use nulastudio\Spider\Contracts\ServiceProviderContract;
use nulastudio\Spider\Exceptions\ServiceProviderRegisterException;
use nulastudio\Spider\Kernel;
use nulastudio\Spider\Services\ExporterService;

class ExporterServiceProvider implements ServiceProviderContract
{
    private $exporterService;

    public function register(Kernel $kernel)
    {
        $this->exporterService               = new ExporterService($kernel->getApplication()->configs['export'] ?? []);
        $kernel->getApplication()->on_export = function ($spider, $config, $data, $request, $response) {
            $exporter = $this->exporterService->getExporter($config['type']);
            if ($exporter) {
                $exporter->export($data);
            } else {
                trigger_error("Can not find a suitable exporter for type {$config['type']}.", E_USER_WARNING);
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
