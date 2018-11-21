<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;

class PrintOutExporter extends AbstructExporter
{
    public function __construct(array $config = [])
    {}
    public function export($data)
    {
        print_r($data);
    }
}
