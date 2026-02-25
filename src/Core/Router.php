<?php 

namespace Core; 

use Core\Middleware;

class Router {
    private array $routes = [];
    private Middleware $middleware;

    public function __construct()
    {
        $this->middleware = new Middleware();
    }


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
     * запускаем роутер - анализируем id ссылки и вызываем нужный контроллер, добавил мидлваре
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

                $controllerMethod = $this->getControllerMethodString($route);
                return $this->middleware->handle($route['path'], $matches, $controllerMethod); 
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
    /**
     *  метод для формирования строки контроллер@метод
     */
    private function getControllerMethodString(array $route):string {
        $classParts = explode('\\', $route['controller']);
        $shortClass = end($classParts);

        return $shortClass . '@' . $route['action'];
    }
}