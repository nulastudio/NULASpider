<?php

namespace nulastudio\Spider\Services;

use nulastudio\Spider\Contracts\ExporterContract;
use nulastudio\Spider\Services\BaseService;

class ExporterService extends BaseService
{
    private $config    = [];
    private $exporters = [];
    private $exporter;
    private $initFailed;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    public function register(string $name, string $exporter)
    {
        $className = ExporterContract::class;

        if (!class_exists($exporter) || !isset(class_implements($exporter)[$className])) {
            // trigger_error("Can not register an invalid exporter: {$exporter}", E_USER_WARNING);
            // FIXME: 使用Exception代替，set_error_handler以及trigger_error未实现
            throw new \Exception("Can not register an invalid exporter: {$exporter}");
            return;
        }
        $this->exporters[$name] = $exporter;
    }
    public function getExporter()
    {
        if ($this->initFailed) return null;
        if (!$this->exporter) {
            try {
                $exporter = $this->exporters[$this->config['type'] ?? ''];
                if (!$exporter) {
                    return;
                }
                $this->exporter = new $exporter($this->config);
            } catch (\Exception $ex) {
                $this->initFailed = true;
                throw $ex;
            }
        }
        return $this->exporter;
    }
}
