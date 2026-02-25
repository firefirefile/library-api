<?php

namespace Models;

class User extends Model
{
    protected static string $table = 'users';
    /**
     * метод поиска по логину
     */
    public static function findByLogin(string $login): ?array
    {

        $sql = 'SELECT * FROM ' . static::$table . ' WHERE login = :login';
        $stmt = self::$db->prepare($sql);
        $stmt->execute(['login' => $login]);

        return $stmt->fetch() ?: null;
    }




};
