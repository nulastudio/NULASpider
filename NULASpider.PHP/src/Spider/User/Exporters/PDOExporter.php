<?php

namespace User\Exporters;

use \PDO;
use nulastudio\Spider\Contracts\AbstructExporter;

class PDOExporter extends AbstructExporter
{
    private $pdo;
    private $table;

    public function __construct(array $config = [])
    {
        try {
            $this->pdo   = new PDO($config['dsn'] ?? '', $config['username'] ?? '', $config['password'] ?? '', $config['options'] ?? []);
            $this->table = $config['table'] ?? '';
        } catch (\Exception $e) {
            $err_code = $e->getCode();
            $err_msg  = $e->getMessage();
            throw new \Exception("Can not connect to the database due to: ERROR ({$err_code}) {$err_msg}", $err_code, $e);
        }
    }
    public function export($data)
    {
        if ($this->pdo) {
            $sql       = "INSERT INTO `{$this->table}` SET ";
            $formatted = [];
            $values    = [];
            foreach ($data as $column => $value) {
                $hash              = md5($column);
                $formatted[]       = "`{$column}` = :{$hash}";
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
