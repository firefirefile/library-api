<?php

namespace Services;

use Models\User;

class AuthService 
{   
    /**
     * метод для регистрации, принимает логин, пароль и подтверждение пароля. 
     * создаёт токен и возвращает его в случае успеха. в случае ошибки возвращает нул 
     */
    public function register(string $login, string $password, string $confirm): ?string  {
        // валидируем входные данные
        $errors = $this->validateRegistration($login, $password, $confirm);
        if (!empty($errors)) {
            error_log('Ошибка в регистрации: ' . implode(',', $errors));
            return null;
        }
        //проверяем уникальность логина
        $exsistUser = User::findByLogin($login);
        if($exsistUser) {
            error_log("Ошибка в регистрации: логин '$login' уже используется");
            return null;
        }
        //хэшируем пароль
        $hashPass = password_hash($password, PASSWORD_DEFAULT);
        //создаём пользователя 
        $userId = User::create([
            'login' => $login,
            'password_hash' => $hashPass
        ]);
        if (!$userId) {
            error_log("Ошибка в регистрации: сохранить запись в базе данных не удалось");
            return null;
        }
        //генерация токена
        $token = $this->generateToken();
        error_log("Успешная регистрация: ID: '$userId', логин: '$login' ");
        return $token;
    }
    
    /**
     * метод для авторизации 
     */
    public function login(string $login, string $password):?string {
        $login = trim($login);
        if(empty($login) || empty($password)) {
            error_log("Ошибка в авторизации: данные для входа не могут быть пустыми");
            return null;
        }
        //проверяем наличие логина в бд
        $user = User::findByLogin($login);
        if(!$user) {
            error_log("Ошибка в авторизации: пользователя с логином '$login' не существует");
            return null;
        }
        //проверяем парол
        if(!password_verify($password, $user['password_hash'])) {
            error_log("Ошибка в авторизации: неверный пароль");
            return null;
        }
        $token = $this->generateToken();
        return $token;
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



        }