<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

define('__ROOT__', __DIR__ . '/../');

require __ROOT__ . '/vendor/autoload.php';

session_cache_limiter('');
session_start();

// Load Dotenv
try {
    $dotenv = new \Dotenv\Dotenv(__ROOT__ . '/');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
} catch(\Dotenv\Exception\InvalidPathException $e) {
    echo "Environment variables file not found.";
    exit;
} catch(\Dotenv\Exception\ValidationException $e) {
    echo "Environment variables not set.";
    exit;
}

// Instantiate the app
$settings = require __ROOT__ . '/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __ROOT__ . '/src/dependencies.php';

// Register middleware
require __ROOT__ . '/src/middleware.php';

// Register routes
require __ROOT__ . '/src/routes.php';

// Run app
$app->run();
