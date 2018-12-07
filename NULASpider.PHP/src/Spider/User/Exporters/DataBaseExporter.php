<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;
use User\Exporters\DataBaseExporterDrivers\DriverInterface;

class DataBaseExporter extends AbstructExporter
{
    private $driver;
    private $table;

    public function __construct(array $config = [])
    {
        $driver = $config['driver'] ?? '';
        if (!$driver) {
            throw new \Exception('You must specify a database driver.');
        }
        $segments = explode('\\', DriverInterface::class);
        array_pop($segments);
        $segments[] = "{$driver}Driver";
        $driverName = implode('\\', $segments);
        if (!class_exists($driverName) || !($this->driver = new $driverName($config))) {
            throw new \Exception("The specified database driver ({$driver}) is invalid.");
        }
        $this->table = $config['table'] ?? '';
    }
    public function export($data)
    {
        if ($this->driver) {
            $this->driver->insert($this->table, $data);
        }
    }
    public function close()
    {
        if ($this->driver) {
            $this->driver->close();
        }
    }
}
