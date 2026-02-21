<?php

namespace Core; 

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null; 

    public static function getConnection(array $config):PDO {
        if (self::$connection === null) {
            $dsn = "mysql:host={$config['host']}; dbname={$config['dbname']};charset=utf8mb4";
            self::$connection = new PDO($dsn, $config['user'], $config['password']);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$connection;
    }
}