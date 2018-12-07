<?php

namespace User\Exporters\DataBaseExporterDrivers;

use User\Exporters\DataBaseExporterDrivers\AbstructDriver;

class MySQLDriver extends AbstructDriver
{
    public function __construct(array $config = [])
    {}
    public function insert($table, $data)
    {}
    public function close()
    {}
}
