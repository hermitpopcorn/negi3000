<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// eloquent ORM
$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

// session
$container['session'] = $container->factory(function ($c) {
    $session_factory = new \Aura\Session\SessionFactory;
    $session = $session_factory->newInstance($_COOKIE);
    $session->setCookieParams(['lifetime' => '86400']);
    return $session;
});

// minifier
$container['minifier/css'] = function ($c) {
    return new \MatthiasMullie\Minify\CSS();
};
$container['minifier/js'] = function ($c) {
    return new \MatthiasMullie\Minify\JS();
};

// http cache
$container['cache'] = function ($c) {
    return new \Slim\HttpCache\CacheProvider();
};

// random string generator
$container['rsgen/identifier'] = $container->factory(function ($c) {
    $factory = new \RandomLib\Factory;
    $generator = $factory->getLowStrengthGenerator();
    return $generator->generateString(8,
        implode(range('a', 'z')) . implode(range('A', 'Z')) . implode(range(0, 9))
    );
});
$container['rsgen/token'] = $container->factory(function ($c) {
    $factory = new \RandomLib\Factory;
    $generator = $factory->getMediumStrengthGenerator();
    return $generator->generateString(16);
});

// handlers
$container['forbiddenHandler'] = function ($c) {
    return function($request, $response) {
        return $response->withStatus(403);
    };
};

// controllers
require __ROOT__ . '/src/controllers/loader.php';

// models
require __ROOT__ . '/src/models/loader.php';
