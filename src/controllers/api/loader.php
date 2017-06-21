<?php

// accounts controller
$container['controllers/api/accounts'] = function($c) {
    require_once __ROOT__ . "/src/controllers/api/Accounts.php";
    return new \App\Controller\API\Accounts($c);
};

// transactions controller
$container['controllers/api/transactions'] = function($c) {
    require_once __ROOT__ . "/src/controllers/api/Transactions.php";
    return new \App\Controller\API\Transactions($c);
};

// statistics controller
$container['controllers/api/stats'] = function($c) {
    require_once __ROOT__ . "/src/controllers/api/Stats.php";
    return new \App\Controller\API\Stats($c);
};

// user-related controller
$container['controllers/api/users'] = function($c) {
    require_once __ROOT__ . "/src/controllers/api/Users.php";
    return new \App\Controller\API\Users($c);
};
