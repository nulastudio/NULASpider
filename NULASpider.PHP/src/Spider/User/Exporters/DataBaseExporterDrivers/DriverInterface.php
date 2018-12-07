<?php

namespace User\Exporters\DataBaseExporterDrivers;

interface DriverInterface
{
    public function __construct(array $config = []);
    public function insert($table, $data);
    public function close();
}
