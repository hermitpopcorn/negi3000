<?php

// require base controller
require_once __ROOT__ . "/src/controllers/BaseController.php";

// assets controller
$container['controllers/assets'] = function($c) {
    require_once __ROOT__ . "/src/controllers/Assets.php";
    return new \App\Controller\Assets($c);
};

// home controller
$container['controllers/home'] = function($c) {
    require_once __ROOT__ . "/src/controllers/Home.php";
    return new \App\Controller\Home($c);
};

// app controller
$container['controllers/app'] = function($c) {
    require_once __ROOT__ . "/src/controllers/App.php";
    return new \App\Controller\App($c);
};

// auth controller
$container['controllers/auth'] = function($c) {
    require_once __ROOT__ . "/src/controllers/Auth.php";
    return new \App\Controller\Auth($c);
};

// load API controllers
require __ROOT__ . "/src/controllers/api/loader.php";
