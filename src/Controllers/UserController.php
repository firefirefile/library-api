<?php

namespace Controllers;

use Exception;
use Models\Access;
use Models\User;

class UserController
{
    /**
     * GET /users - список всех участников
     */
    public function getUsers(): void
    {
        try {
            $users = User::all();

            $result = array_map(function ($user) {
                return [
                    'id' => $user['id'],
                    'login' => $user['login']
                ];
            }, $users);

            http_response_code(200);
            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch users'
            ]);
        }
    }

    /**
    * POST /users/{id}/grant - дать доступ к библиотеке
    */
    public function grantAccess(int $ownerId): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $guestId = $input['user_id'] ?? 0;

            if (!$guestId) {
                throw new Exception('User ID is required', 400);
            }

            $result = Access::grant($ownerId, $guestId);

            if ($result) {
                http_response_code(200);
                header('Content-Type: application/json');

                echo json_encode([
                    'success' => true,
                    'message' => 'Access granted'
                ]);
            } else {
                throw new Exception('Failed to grant access', 500);
            }

        } catch (Exception $e) {
            $code = $e->getCode();
            if (!is_int($code)) {
                $code = 400;
            }
            http_response_code($code);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e -> getMessage()
            ]);
        }

    }
}
