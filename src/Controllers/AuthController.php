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

        $login = trim($input['login'] ?? '');
        $password = trim($input['password'] ?? '');
        // Accept both 'confirm' and 'confirm_password' for flexibility
        $confirm = trim($input['confirm_password'] ?? '');

        try {
            $token = $this->authService->register($login, $password, $confirm);

            http_response_code(201);
            header('Content-Type: application/json');
            
            echo json_encode([
                'success' => true,
                'token' => $token
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

     public function login():void {
        $input = json_decode(file_get_contents('php://input'), true);

        $login = trim($input['login'] ?? '');
        $password = trim($input['password'] ?? '');

        try {
            $token = $this->authService->login($login, $password);

            http_response_code(200);
            header('Content-Type: application/json');
            
            echo json_encode([
                'success' => true,
                'token' => $token
            ]);
        } catch(Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    
};