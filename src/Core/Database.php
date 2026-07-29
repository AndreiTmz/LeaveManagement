<?php

namespace LeaveManagement\Core;

use PDO;

class Database
{
    private PDO $connection;

    public function __construct(array $config)
    {
         $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['dbname']
        );

         $this->connection = new PDO(
            $dsn,
            $config['user'],
            $config['password']
        );    

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}