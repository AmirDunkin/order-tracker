<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

$environment = app_environment();

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

if ($environment === 'local') {
    return [
        'driver'   => 'mysql',
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_DATABASE') ?: 'order_tracker',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset'  => 'utf8mb4',
        'options'  => $options,
    ];
}

// Production (cPanel shared hosting)
return [
    'driver'   => 'mysql',
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => (int) (getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'yourcpanel_order_tracker',
    'username' => getenv('DB_USERNAME') ?: 'yourcpanel_dbuser',
    'password' => getenv('DB_PASSWORD') ?: 'CHANGE_ME',
    'charset'  => 'utf8mb4',
    'options'  => $options,
];
