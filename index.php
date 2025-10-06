<?php

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Router;

$router = new Router();

$router->get('/', [AuthController::class, 'show']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');
