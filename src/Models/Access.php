<?php

namespace Models;

class Access extends Model {
    protected static string $table = 'access';

    /**
     * метод для предоставления доступа к библиотеке
     */
    public static function grant (int $ownerId, int $guestId):bool {
        $sql = "SELECT id FROM " . static::$table . " 
        WHERE owner_id = :owner_id 
        AND guest_id = :guest_id";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            'owner_id' => $ownerId,
            'guest_id' => $guestId
            ]);

            if ($stmt->fetch()) {
                return false;
            }

            return self::create([
            'owner_id' => $ownerId,
            'guest_id' => $guestId
            ]) > 0;
    }
    /** 
     * метод для проверки, был ли выдан доступ к библиотеке 
     */
    public static function hasAccess (int $ownerId, int $guestId):bool {
        $sql = "SELECT id FROM " . static::$table . " 
        WHERE owner_id = :owner_id 
        AND guest_id = :guest_id";

        $stmt = self::$db->prepare($sql);
        $stmt->execute([
            'owner_id' => $ownerId,
            'guest_id' => $guestId
            ]);

            return (bool) $stmt->fetch();

    }
    /** 
     * метод для запроса списка участников (для списка Кому я дал доступ к своей библиотеке)
     */
    public static function getGuests(int $ownerId):array {
        $sql = 'SELECT u.id, u.login 
            FROM users u 
            JOIN access a ON u.id = a.guest_id   
            WHERE a.owner_id = :owner_id';  
                  
    $stmt = self::$db->prepare($sql);
    $stmt->execute(['owner_id' => $ownerId]);
    return $stmt->fetchAll();
    }

}