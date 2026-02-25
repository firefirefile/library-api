<?php

namespace Models;

use Core\Database;
use PDO; 

abstract class Model {
    protected static PDO $db;
    protected static string $table;
    /**
     * Сохраняет подключение к БД в статическое свойство, чтобы все модели использовали одно соединение.
     * @param $connection - подключение к базе данных 
     */
    public static function getConnection(PDO $connection) {
        self::$db = $connection;
    }
    /**
     * Ищет запись по ID в соответствующей таблице.
     * @param id - айди пользователя 
     */
    public static function find($id) {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE id = :id';
        $stmt = self::$db->prepare($sql);
        $stmt->execute(['id' => $id]); 
        
        return $stmt->fetch();
    }
    /**
     * Возвращает все записи из таблицы
     * @example User::all() вернет всех пользователей
     */
    public static function all() {
        $sql = 'SELECT * FROM ' . static::$table;
        $stmt = self::$db->query($sql);
        
        return $stmt->fetchAll();
    }
   
    /**
     * создаёт новую запись в таблицу, возвращает айди созданной записи 
     */
    public static function create (array $data):int {
        $table = static::$table;
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) =>":{$field}", $fields);

        $sql = "INSERT INTO {$table}
        (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($data);

        return (int) self::$db->lastInsertId();
    }

    /**
     * обновляет запись в таблице, возвращает тру, если обновил 
     */
    public static function update (int $id, array $data):bool {
        $table = static::$table;

        $setPart = array_map(
            fn($field) => "{$field} = :{$field}",
            array_keys($data)
        );

        $setString = implode(', ', $setPart);

        $sql = "UPDATE {$table} SET {$setString} WHERE id = :id";
        $data['id'] = $id; 

        $stmt = self::$db->prepare($sql);
        return  $stmt->execute($data);
    }
    /**
     * удаление с восстановлением - возвращает тру, если всё получилось
     */
    public static function softDelete (int $id):bool {
        $sql = "UPDATE " . static::$table . " SET deleted_at = NOW() WHERE id = :id";
         $stmt = self::$db->prepare($sql);
         $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
    /**
     * восстановление после удаления - опять же возвращает тру при успехе 
     */
    public static function restore (int $id):bool {
        $sql = "UPDATE " . static::$table . " SET deleted_at = NULL WHERE id = :id";
         $stmt = self::$db->prepare($sql);
         $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    protected static function getDB():PDO {
        return Database::getConnection();
    }

    
};