<?php 

namespace Core; 

class Router {
    private array $routes = [];


    /**
     *  метод добавляет новый маршрут
     * @param string $method HTTP метод 
     * @param string $path URL паттерн с параметрами в фигурных скобках
     * @param array $handler [класс контроллера, название метода]
     */
    public function add(string $method, string $path, array $handler): void {
        $this->routes[] = [
            'method'=> strtoupper($method),
            'path' => $path,
            'controller' => $handler[0], 
            'action' => $handler[1]
        ];
    }
    /**
     * запускаем роутер - анализируем id ссылки и вызываем нужный контроллер 
     * @param $uri запрашиваемый url 
     * @param $method HTTP метод запроса 
     */
    public function dispatch(string $uri, string $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if($route['method'] !== $method) {
                continue; 
            }

            $pattern = $this->convertToPattern($route['path']);
            if(preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $controller = new $route['controller'](); 
                call_user_func_array([$controller, $route['action']], $matches);

                return;
            }
        }

        http_response_code(404);
        echo json_encode([
            'error' => 'Route not found'
        ]);

    }

    /**
     * преобразуем URL паттерн с параметрами в регулярное выражение
     */
     private function convertToPattern(string $path): string {
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}