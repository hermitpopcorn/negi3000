<?php

// users model
$container['models/users'] = function($c) {
    $db = $c->get('db');
    require_once __ROOT__ . "/src/models/User.php";
    require_once __ROOT__ . "/src/models/Account.php";
    require_once __ROOT__ . "/src/models/Transaction.php";
    require_once __ROOT__ . "/src/models/Tag.php";
    return new \App\Model\User();
};

// transactions model
$container['models/transactions'] = function($c) {
    $db = $c->get('db');
    require_once __ROOT__ . "/src/models/Account.php";
    require_once __ROOT__ . "/src/models/Tag.php";
    require_once __ROOT__ . "/src/models/Transaction.php";
    return new \App\Model\Transaction();
};

// tags model
$container['models/tags'] = function($c) {
    $db = $c->get('db');
    require_once __ROOT__ . "/src/models/Transaction.php";
    require_once __ROOT__ . "/src/models/Tag.php";
    return new \App\Model\Tag();
};

// transactions x tags model
$container['models/transactions-tags'] = function($c) {
    $db = $c->get('db');
    require_once __ROOT__ . "/src/models/Account.php";
    require_once __ROOT__ . "/src/models/Tag.php";
    require_once __ROOT__ . "/src/models/Transaction.php";
    require_once __ROOT__ . "/src/models/TransactionTag.php";
    return new \App\Model\TransactionTag();
};

// accounts model
$container['models/accounts'] = function($c) {
    $db = $c->get('db');
    require_once __ROOT__ . "/src/models/Tag.php";
    require_once __ROOT__ . "/src/models/User.php";
    require_once __ROOT__ . "/src/models/Transaction.php";
    require_once __ROOT__ . "/src/models/Account.php";
    return new \App\Model\Account();
};
