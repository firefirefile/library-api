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
        ' WHERE user_id = :user_id AND is_deleted = FALSE';
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
        AND is_deleted = FALSE";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'query' => "%$query%"
            ]);
        return $stmt->fetchAll();
    }

    /**
     * удаление 
     */
    public static function softDelete(int $id):bool {
        $sql = "UPDATE " . static::$table . " SET is_deleted = TRUE WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * восстановление - меняет is_deleted обратно на false 
     */
    public static function restore(int $id):bool {
        $sql = "UPDATE " . static::$table . " SET is_deleted = FALSE WHERE id = :id";
        $stmt = self::$db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }



};