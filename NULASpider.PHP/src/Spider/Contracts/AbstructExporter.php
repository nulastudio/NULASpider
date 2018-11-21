<?php

namespace nulastudio\Spider\Contracts;

use nulastudio\Spider\Contracts\ExporterContract;

abstract class AbstructExporter implements ExporterContract
{
    abstract public function __construct(array $config = []);
    abstract public function export($data);
    public function handleUnsuppertedData($data)
    {
        return is_array($data) ? json_encode($data) : (string) $data;
    }
}
