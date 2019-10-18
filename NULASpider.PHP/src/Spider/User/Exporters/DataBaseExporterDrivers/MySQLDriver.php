<?php

namespace User\Exporters\DataBaseExporterDrivers;

use \PDO;
use User\Exporters\DataBaseExporterDrivers\AbstructDriver;

class MySQLDriver extends AbstructDriver
{
    private $pdo;
    private $table;
    private $charset;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        try {
            if (!isset($config['host']) ||!isset($config['dbname'])) {
                throw new \Exception('empty host or empty dbname');
            }
            $segments = [
                "host"   => $config['host'],
                "port"   => $config['port'] ?? 3306,
                "dbname" => $config['dbname'],
            ];
            $username      = $config['username'] ?? null;
            $password      = $config['password'] ?? null;
            $this->charset = strtoupper($config['charset'] ?? 'UTF-8');
            if ($this->charset === 'UTF-8') {
                $this->charset = 'UTF8';
            }
            $dsn = 'mysql:';
            foreach ($segments as $k => $v) {
                $dsn .= "{$k}={$v};";
            }
            $this->pdo = new PDO($dsn, $username, $password, [
                // PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->charset}'",
            ]);
            $this->table = $config['table'] ?? '';
        } catch (\Exception $e) {
            $err_code = $e->getCode();
            $err_msg  = $e->getMessage();
            throw new \Exception("Can not connect to the database due to: ERROR ({$err_code}) {$err_msg}", $err_code, $e);
        }
    }
    public function insert($table, $data)
    {
        if ($this->pdo) {
            $sql       = "INSERT INTO `{$this->table}` SET ";
            $formatted = [];
            $values    = [];
            foreach ($data as $column => $value) {
                $hash               = md5($column);
                $formatted[]        = "`{$column}` = :{$hash}";
                $values[":{$hash}"] = $value;
            }
            $sql .= implode(', ', $formatted);
            if ($statement = $this->pdo->prepare($sql)) {
                if (!$statement->execute($values)) {
                    throw new \Exception("Can not export to database due to: ERROR ({$statement->errorCode()}) {$statement->errorInfo()}", $statement->errorInfo());
                }
            } else {
                throw new \Exception("Can not generate sql due to: ERROR ({$this->pdo->errorCode()}) {$this->pdo->errorInfo()}", $this->pdo->errorInfo());
            }
            $statement = null;
        }
    }
    public function close()
    {
        $this->pdo = null;
    }
}
