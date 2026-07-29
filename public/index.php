<?php

declare(strict_types=1);
session_start();

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Core/Helpers.php';

use LeaveManagement\Core\Router;
use LeaveManagement\Core\Container;
use LeaveManagement\Controllers\HomeController;
use LeaveManagement\Controllers\AuthController;
use LeaveManagement\Controllers\EmployeeController;
use LeaveManagement\Controllers\DashboardController;
use LeaveManagement\Controllers\LeaveController;

$config = require __DIR__ . '/../config/database.php';

$container = new Container();
    
// load DI config
$register = require __DIR__ . '/../config/container.php';
$register($container, $config);

$router = new Router($container);

$router->get('/', [$container->get(HomeController::class), 'index']);

$router->get('/login', [$container->get(AuthController::class), 'showLogin']);
$router->post('/login', [$container->get(AuthController::class), 'login']);
$router->post('/logout', [$container->get(AuthController::class), 'logout']);

$router->get('/employees', [$container->get(EmployeeController::class), 'index']);

$router->get('/calendar', [$container->get(DashboardController::class), 'index']);
$router->get('/calendar/leaves', [$container->get(DashboardController::class), 'getLeaveEvents']);

$router->get('/leaves/request', [$container->get(LeaveController::class), 'showRequestLeave']);
$router->post('/leaves/request', [$container->get(LeaveController::class), 'requestLeave']);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);