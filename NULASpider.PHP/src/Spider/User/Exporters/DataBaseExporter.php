<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;

class DataBaseExporter extends AbstructExporter
{
    private $drivers = [
        'mysql' => \User\Exporters\DataBaseExporterDrivers\MySQLDriver::class,
    ];
    private $driver;
    private $table;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $driver = $config['driver'] ?? '';
        if (!$driver) {
            throw new \Exception('You must specify a database driver.');
        }
        $driver     = strtolower($driver);
        $driverName = $this->drivers[$driver] ?? '';
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
