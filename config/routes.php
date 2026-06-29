<?php

declare(strict_types=1);

/** @var \Core\Router $router */

$router->get('/', 'HomeController', 'index');

$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');

$router->get('/customer/orders', 'CustomerController', 'index');
$router->get('/customer/orders/create', 'CustomerController', 'create');
$router->post('/customer/orders', 'CustomerController', 'store');
$router->get('/customer/orders/{id}', 'CustomerController', 'show');

$router->get('/shopper/dashboard', 'ShopperController', 'dashboard');
$router->get('/shopper/orders', 'ShopperController', 'orders');
$router->get('/shopper/orders/{id}', 'ShopperController', 'show');
$router->post('/shopper/orders/{id}/status', 'ShopperController', 'updateStatus');

$router->post('/api/ai-suggest', 'ApiController', 'aiSuggest');
