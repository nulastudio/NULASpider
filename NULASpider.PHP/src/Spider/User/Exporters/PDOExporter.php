<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;
use \PDO;

class PDOExporter extends AbstructExporter
{
    // 你好
    private $pdo;
    private $table;
    private $columns;

    public function __construct(array $config = [])
    {
        try {
            $this->pdo   = new PDO($config['dsn'] ?? '', $config['username'] ?? '', $config['password'] ?? '', $config['options'] ?? []);
            $this->table = $config['table'] ?? '';
            if ($statement = $this->pdo->query("DESC `{$this->table}`")) {
                $this->columns = array_map(function ($row) {
                    return $row['Field'];
                }, $statement->fetchAll(PDO::FETCH_ASSOC));
                $statement = null;
                $statement = $this->pdo->query('SET NAMES utf8');
                if ($statement) {
                    $statement->fetchAll();
                }
                $statement = null;
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                throw new \Exception("Error Processing Request", 1);
            }
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
            foreach ($this->columns as $column) {
                if (isset($data[$column])) {
                    $formatted[]          = "{$column} = :{$column}";
                    $values[":{$column}"] = is_array($data[$column]) ? json_encode($data[$column]) : $data[$column] ?? '';
                }
            }
            $sql .= implode(', ', $formatted);
            $statement = $this->pdo->prepare($sql);
            var_dump($statement);
            $statement->execute($values);
            // if ($statement = $this->pdo->prepare($sql)) {
            //     if (!$statement->execute($values)) {
            //         // trigger_error();
            //     }
            // } else {
            //     var_dump('dwad');
            // }
            $statement = null;
        }
    }
    public function close()
    {
        $this->pdo = null;
    }
}
