<?php


$config = require __DIR__ . '/src/Config/database.php';
$sqlFolder = __DIR__ . '/migrations/';

define('DB_HOST', $config['host']);
define('DB_USER', $config['user']);
define('DB_PASSWORD', $config['password']);
define('DB_NAME', $config['dbname']);
define('DB_TABLE_VERSIONS', 'versions');



function connectDB() {
    $error_message = 'Невозможно подключиться к серверу базы данных';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$conn) {
        throw new Exception($error_message);
    } else {
        $query = $conn -> query('set names utf8');
        if (!$query) 
            throw new Exception($error_message);
        else 
                return $conn;
        
    }
};

// Получаем список файлов для миграций
function getMigrationFiles($conn, $sqlFolder) {
    $allFiles = glob($sqlFolder . '*.sql');
    $query = sprintf('show tables from `%s` like "%s"', DB_NAME, DB_TABLE_VERSIONS); 
    $data = $conn->query($query);
    $firstMigration = !$data->num_rows;

    if ($firstMigration) {
        return ($allFiles);
    }

    $versionFiles = array();

    $query = sprintf('select `name` from `%s`', DB_TABLE_VERSIONS);
    $data = $conn->query($query) -> fetch_all(MYSQLI_ASSOC);

    foreach ($data as $row) {
        array_push($versionFiles, $sqlFolder . $row['name']);
    }
    return array_diff($allFiles, $versionFiles);
}
 
 
// Накатываем миграцию файла
function migrate($conn, $file) {
    $command = sprintf('mysql -u%s -p%s -h %s -D %s < %s', DB_USER, DB_PASSWORD, DB_HOST, DB_NAME, $file);
    shell_exec($command);

    $baseName = basename($file); 
    $query = sprintf('insert into `%s` (`name`) values("%s")', DB_TABLE_VERSIONS, $baseName);
    $conn->query($query);
}
 
 
// Стартуем
 
// Подключаемся к базе
$conn = connectDB();
 
// Получаем список файлов для миграций за исключением тех, которые уже есть в таблице versions
$files = getMigrationFiles($conn, $sqlFolder);
 
// Проверяем, есть ли новые миграции
if (empty($files)) {
    echo 'Ваша база данных в актуальном состоянии.';
} else {
    echo 'Начинаем миграцию...<br><br>';
 
    // Накатываем миграцию для каждого файла
    foreach ($files as $file) {
        migrate($conn, $file);
        // Выводим название выполненного файла
        echo basename($file) . '<br>';
    }
 
    echo '<br>Миграция завершена.';    
}