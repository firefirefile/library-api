<?php

namespace Models;

class Token extends Model
{
    protected static string $table = 'tokens';

    public static function findByToken(string $token): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;

    }

    public static function deleteExpired(): int
    {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM " . static::$table . " WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
