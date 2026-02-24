<?php

namespace Core; 

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $connection = null; 
    private static array $config = []; 

    public static function init(array $config): void {
        self::$config = $config;
    }

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            if (empty(self::$config)) {
                throw new Exception('database config not loaded, call Database::init($config) first');
            }

            $required = ['host', 'dbname', 'user', 'password'];
            foreach ($required as $field) {
                if (!isset(self::$config[$field])) {
                    throw new Exception("config field '{$field}' is missing");
                }
            }

            // Собираем DSN
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                trim(self::$config['host']),
                trim(self::$config['dbname'])
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Failed to connect to database");
            }
        }
        return self::$connection;
    }
}