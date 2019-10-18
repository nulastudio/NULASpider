<?php

namespace nulastudio\Spider\Contracts;

use nulastudio\Spider\Contracts\ExporterContract;

abstract class AbstructExporter implements ExporterContract
{
    public function __construct(array $config = [])
    {}
    abstract public function export($data);
    public function handleUnsupportedData($data)
    {
        return is_array($data) ? json_encode($data) : (string) $data;
    }
}
