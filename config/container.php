<?php

use LeaveManagement\Core\Database;
use LeaveManagement\Repositories\EmployeeRepository;
use LeaveManagement\Repositories\LeaveRepository;
use LeaveManagement\Services\AuthService;
use LeaveManagement\Services\EmployeeService;
use LeaveManagement\Services\LeaveService;
use LeaveManagement\Controllers\AuthController;
use LeaveManagement\Controllers\EmployeeController;
use LeaveManagement\Controllers\HomeController;
use LeaveManagement\Controllers\DashboardController;
use LeaveManagement\Controllers\LeaveController;

return function ($container, $config) {

    $container->set(Database::class, function () use ($config) {
        return new Database($config);
    });

    $container->set(EmployeeRepository::class, function ($c) {
        return new EmployeeRepository(
            $c->get(Database::class)->getConnection()
        );
    });

    $container->set(EmployeeService::class, function ($c) {
        return new EmployeeService(
            $c->get(EmployeeRepository::class)
        );
    });

    $container->set(EmployeeController::class, function ($c) {
        return new EmployeeController(
            $c->get(EmployeeService::class)
        );
    });

    $container->set(HomeController::class, fn() => new HomeController());

    $container->set(AuthService::class, function ($c) {
        return new AuthService(
            $c->get(EmployeeRepository::class)
        );
    });

    $container->set(AuthController::class, function ($c) {
        return new AuthController(
            $c->get(AuthService::class)
        );
    });

    $container->set(LeaveRepository::class, function ($c) {
        return new LeaveRepository(
            $c->get(Database::class)->getConnection()
        );
    });

    $container->set(LeaveService::class, function ($c) {
        return new LeaveService(
            $c->get(LeaveRepository::class)
        );
    });

    $container->set(DashboardController::class, function ($c) {
        return new DashboardController(
            $c->get(LeaveService::class)
        );
    });

    $container->set(LeaveController::class, function ($c) {
        return new LeaveController(
            $c->get(LeaveService::class)
        );
    });
};