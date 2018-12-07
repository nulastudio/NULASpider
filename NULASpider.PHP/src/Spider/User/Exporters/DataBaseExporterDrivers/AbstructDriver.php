<?php

namespace User\Exporters\DataBaseExporterDrivers;

use User\Exporters\DataBaseExporterDrivers\DriverInterface;

abstract class AbstructDriver implements DriverInterface
{
    abstract public function __construct(array $config = []);
    abstract public function insert($table, $data);
    abstract public function close();
}
