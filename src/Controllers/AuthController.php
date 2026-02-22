<?php

namespace Controllers; 

use Services\AuthService;
use Exception;

class AuthController {
    private $authService;

    public function __construct()
    {
       $this->authService = new AuthService();
    }

    public function register():void {
        $input = json_decode(file_get_contents('php://input'), true);

        $login = $input['login'] ?? '';
        $password = $input['password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        try {
            $token = $this->authService->register($login, $password, $confirm);

            http_response_code(201);
            header('Content-Type: application/json');
            
            echo json_encode([
                'success' => true,
                'token' => $token
            ]);
        } catch(Exception $el) {
            http_response_code($el -> getCode() ?: 400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $el -> getMessage()
            ]);
        }
    }

     public function login():void {
        $input = json_decode(file_get_contents('php://input'), true);

        $login = $input['login'] ?? '';
        $password = $input['password'] ?? '';
  

        try {
            $token = $this->authService->login($login, $password);

            http_response_code(200);
            header('Content-Type: application/json');
            
            echo json_encode([
                'success' => true,
                'token' => $token
            ]);
        } catch(Exception $el) {
            http_response_code($el -> getCode() ?: 400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $el -> getMessage()
            ]);
        }
    }

    
};