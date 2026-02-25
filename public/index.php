<?php 

//точка входа в приложение: подключает автозагрузку классов и запускает роутер 


spl_autoload_register(function ($class) {
    // маппинг неймспейсов 
    $prefixes = [
        'Controllers\\' => __DIR__ . '/../src/Controllers/',
        'Models\\' => __DIR__ . '/../src/Models/',
        'Services\\' => __DIR__ . '/../src/Services/',
        'Core\\' => __DIR__ . '/../src/Core/'
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});


use Core\Router;
use Controllers\AuthController;
use Controllers\BookController;
use Controllers\UserController;
use Core\Database;
use Models\Model;

$config = require __DIR__ . '/../src/Config/database.php';
Database::init($config);
Model::getConnection(Database::getConnection());

$router = new Router(); 

$routes = [
    //публичные маршруты 
    ['POST', '/register', [AuthController::class, 'register']],
    ['POST', '/login', [AuthController::class, 'login']],
    //защищенные маршруты 
    //user
    ['GET', '/users', [UserController::class, 'getUsers']],
    ['POST', '/users/{id}/grant', [UserController::class, 'grantAccess']],
    //books
    ['GET', '/books', [BookController::class, 'getUserBooks']],
    ['POST', '/books', [BookController::class, 'createBook']],
    ['GET', '/books/search', [BookController::class, 'searchGoogleBooks']],
    ['POST', '/books/import', [BookController::class, 'saveFoundBook']],
    ['GET', '/books/{id}', [BookController::class, 'getBook']],
    ['PUT', '/books/{id}', [BookController::class, 'updateBook']],
    ['DELETE', '/books/{id}', [BookController::class, 'deleteBook']],
    ['POST', '/books/{id}/restore', [BookController::class, 'restoreBook']],
    ['GET', '/users/{id}/books', [BookController::class, 'getUserBooksByAccess']]
    ];

    foreach($routes as $route) {
        $router->add($route[0], $route[1], $route[2]);
    }
    

//запуск: берем текущий юрл и метод запроса, ищем совпадение в маршрутах, вызываем метод контроллера 

$uri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($basePath, '/');
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$router->dispatch($uri, $_SERVER['REQUEST_METHOD']);