<?php

namespace nulastudio\Spider\Services;

use nulastudio\Spider\Contracts\ExporterContract;
use nulastudio\Spider\Services\BaseService;

class ExporterService extends BaseService
{
    private $config    = [];
    private $exporters = [];
    private $exporter;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    public function register(string $name, string $exporter)
    {
        $className = ExporterContract::class;
        # warning this is a bug!
        $className = array_reverse(explode('\\', $className))[0];

        if (!class_exists($exporter) || !isset(class_implements($exporter)[$className])) {
            trigger_error("Can not register an invalid exporter: {$exporter}", E_USER_WARNING);
            return;
        }
        $this->exporters[$name] = $exporter;
    }
    public function getExporter()
    {
        if (!$this->exporter) {
            $exporter = $this->exporters[$this->config['type'] ?? ''];
            if (!$exporter) {
                return;
            }
            $this->exporter = new $exporter($this->config);
        }
        return $this->exporter;
    }
}
