<?php

declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';
$dbConfig = require BASE_PATH . '/config/database.php';

require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Model.php';
require BASE_PATH . '/core/Controller.php';
require BASE_PATH . '/core/Router.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

\Core\Database::connect($dbConfig);

$router = new \Core\Router($config);

require BASE_PATH . '/config/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper((string) $_POST['_method']);
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = parse_url($config['app']['url'], PHP_URL_PATH) ?: '';

if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}

$router->dispatch($method, $uri);
