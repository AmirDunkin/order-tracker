<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

$environment = app_environment();

return [
    'app' => [
        'name'     => 'OrderTrack',
        'version'  => '1.0.0',
        'env'      => $environment,
        'debug'    => $environment === 'local',
        'url'      => app_base_url(),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kuala_Lumpur',
    ],
    'paths' => [
        'root'   => dirname(__DIR__),
        'app'    => dirname(__DIR__) . '/app',
        'core'   => dirname(__DIR__) . '/core',
        'views'  => dirname(__DIR__) . '/app/Views',
        'public' => dirname(__DIR__) . '/public',
    ],
];
