<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\ExporterContract;
use User\Exporters\ExporterInterface;

class PrintOutExporter implements ExporterContract
{
    public function __construct(array $config = [])
    {}
    public function export($data)
    {
        print_r($data);
    }
}
