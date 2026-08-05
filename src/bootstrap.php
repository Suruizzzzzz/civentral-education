<?php

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load .env variables (no local database connection — all data comes from the remote API)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load Repositories
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/PermissionRepository.php';

// Load Services
require_once __DIR__ . '/Services/AuthService.php';
require_once __DIR__ . '/Services/UserService.php';
require_once __DIR__ . '/Services/PermissionService.php';
require_once __DIR__ . '/Services/HeaderService.php';

// Load Middleware
require_once __DIR__ . '/Middleware/SessionTimeout.php';
require_once __DIR__ . '/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/Middleware/PermissionMiddleware.php';

// Initialize Core & Middleware
$authService = new \App\Services\AuthService();

// Support dynamic basePath if defined before requiring bootstrap.php
$currentBasePath = $basePath ?? '../';
$sessionTimeout = new \App\Middleware\SessionTimeout(1800, $currentBasePath);
$sessionTimeout->handle();

// Initialize Repositories (no local DB — they use the remote API via session cache)
$userRepo = new \App\Repositories\UserRepository(null);
$permRepo = new \App\Repositories\PermissionRepository(null);

// Initialize Services
$userService = new \App\Services\UserService($userRepo);
$permService = new \App\Services\PermissionService($permRepo);

// Initialize Header Service (and build user)
$headerService = new \App\Services\HeaderService($userService, $permService, $authService);
$headerUser = $headerService->buildHeaderUser();
