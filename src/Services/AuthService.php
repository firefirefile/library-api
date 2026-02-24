<?php

namespace Services;

use Core\Database;
use Models\User;
use Models\Token;
use Exception; 

class AuthService 
{   
    /**
     * метод для регистрации, принимает логин, пароль и подтверждение пароля. 
     * создаёт токен и возвращает его в случае успеха. в случае ошибки возвращает нул 
     */
    public function register(string $login, string $password, string $confirm): array  {
        // валидируем входные данные
        $errors = $this->validateRegistration($login, $password, $confirm);
        if (!empty($errors)) {
             throw new Exception(implode(', ', $errors), 400);
        }
        //проверяем уникальность логина
        $exsistUser = User::findByLogin($login);
        if($exsistUser) {
            throw new Exception("Логин '$login' уже используется", 409);
        }
        //хэшируем пароль
        $hashPass = password_hash($password, PASSWORD_DEFAULT);
        //создаём пользователя 
        $userId = User::create([
            'login' => $login,
            'password_hash' => $hashPass
        ]);
        if (!$userId) {
            throw new Exception("Не удалось создать пользователя", 500);
        }
        //генерация и сохранение токена
        $token = $this->generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days')); 

         $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, $expiresAt]);

        error_log("Успешная регистрация! ID: '$userId', login: '$login' ");

        return [
            'user_id' => $userId,
            'token' => $token
        ];
        
    }
    
    /**
     * метод для авторизации 
     */
    public function login(string $login, string $password):?array {
        $login = trim($login);
        if(empty($login) || empty($password)) {
            throw new Exception("Логин и пароль не могут быть пустыми", 400);
        }
        //проверяем наличие логина в бд
        $user = User::findByLogin($login);
        if(!$user) {
            throw new Exception("Неверный логин или пароль", 401);
        }
        //проверяем парол
        if(!password_verify($password, $user['password_hash'])) {
            throw new Exception("Неверный логин или пароль", 401);
        }

        //генерируем токен
        $token = $this->generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        //сохраняем токен в БД 
         $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expiresAt]);

        return  [
            'user_id' => $user['id'],
            'token' => $token
        ];


       
    }
  
        /**
         * метод для проверки вводимых данных: проверяет логин, пароль, а также проверяет чтобы пароль и его подтверждение совпадали
         */
    private function validateRegistration(string $login, string $password, string $confirm):array  {
            $errors = [];
            $login = trim($login);
            if (empty($login)) {
                $errors[] = 'Логин не может быть пустым';
            }
            elseif (strlen($login) < 3) {
                $errors[] = 'Логин не может быть короче трёх символов';
            }
            elseif (strlen($login) > 30) {
                $errors[] = 'Логин не может быть длиннее тридцати символов';
            }
            elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
                $errors[] = 'Логин может содержать только буквы, цифры и подчеркивания';
            }

            if(empty($password)) {
                $errors[] = 'Пароль не может быть пустым';
            }
            elseif (strlen($password) < 6) {
                $errors[] = 'Пароль не может быть короче шести символов';
            }

            if($password !== $confirm) {
                $errors[] = 'Пароли должны совпадать';
            }

            return $errors;
        }
    /**
     * метод для создания токена 
     */
    private function generateToken() {
        return bin2hex(random_bytes(32));

    }
    /**
     * метод для проверки токена - проверяем что он есть и что не истёк
     */
    public function validateToken($token):?int {
        $tokenData = Token::findByToken($token);

        if(!$tokenData || strtotime($tokenData['expires_at']) < time()) {
            return null; 
        }

        return (int)$tokenData['user_id'];

    }
        }