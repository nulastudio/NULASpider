<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;

class PrintOutExporter extends AbstructExporter
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }
    public function export($data)
    {
        print_r($data);
    }
    public function handleUnsupportedData($data)
    {
        return $data;
    }
}
