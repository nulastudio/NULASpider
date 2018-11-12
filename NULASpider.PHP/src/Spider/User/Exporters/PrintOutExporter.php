<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\ExporterContract;

class PrintOutExporter implements ExporterContract
{
    public function __construct(array $config = [])
    {}
    public function export($data)
    {
        print_r($data);
    }
}
