<?php 

namespace Models;

class Book extends Model {
     protected static string $table = 'books';

 // методы для работы с книгами 
    /**
     * запрашивает книги конкретного пользователя для списка моих книг 
     */
    public static function findByUserId(int $userId):array {
        $sql = "SELECT * FROM " . static::$table . 
        ' WHERE user_id = :user_id AND deleted_at IS NULL';
        $stmt = self::$db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(); 
    }
        /**
         * поиск книги по названию
         */
    public static function searchByTitle(string $query, int $userId):array {
        $sql = "SELECT * FROM " . static::$table . " 
        WHERE user_id = :user_id 
        AND title LIKE :query 
        AND deleted_at IS NULL";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'query' => "%$query%"
            ]);
        return $stmt->fetchAll();
    } 



};