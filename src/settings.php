<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __ROOT__ . '/templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'negi3000',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __ROOT__ . '/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Database settings
        'db' => [
            'driver' => getenv('DB_DRIVER') ? getenv('DB_DRIVER') : 'mysql',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'timezone' => '+07:00',
        ],
    ],
];
