<?php

namespace Core;

use Services\AuthService;

class Middleware
{
    private AuthService $authService;
    private array $publicRoutes = ['/register', '/login'];

    public function __construct()
    {
        $this->authService = new AuthService();
    }


    public function handle(string $route, array $params, string $controllerMethod): mixed
    {
        if ($this->isPublicRoute($route)) {
            return $this->callController($controllerMethod, $params);
        }

        $token = $this->getBearerToken();

        if (!$token) {
            $this->jsonResponse(
                401,
                ['error' => "Token not provided"]
            );
        }

        $userId = $this->authService->validateToken($token);

        if (!$userId) {
            $this->jsonResponse(
                401,
                ['error' => "Invalid or expired token"]
            );
        }

        array_unshift($params, $userId);

        return $this->callController($controllerMethod, $params);
    }

    private function callController(string $controllerMethod, array $params): mixed
    {
        list($controller, $method) = explode('@', $controllerMethod);
        $controllerClass = "Controllers\\$controller";

        if (!class_exists($controllerClass)) {
            $this->jsonResponse(
                500,
                ['error' => "Controller $controller was not found"]
            );
        }

        $controllerInstanse = new $controllerClass();

        if (!method_exists($controllerInstanse, $method)) {
            $this->jsonResponse(
                500,
                ['error' => "Method $method was not found"]
            );
        }

        return call_user_func_array([$controllerInstanse, $method], $params);

    }

    /**
     * проверяет публичный маршрут
     */
    private function isPublicRoute(string $route): bool
    {
        return in_array($route, $this->publicRoutes);
    }

    /**
     * вытаскивает токен из заголовка
     */
    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matсhes)) {
            return $matсhes[1];
        }

        return null;
    }





    private function jsonResponse(int $code, array $data): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;

    }


}
