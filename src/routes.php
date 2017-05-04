<?php
// Routes

$app->get('/', 'controllers/home:index')->setName('home')->add($middleware['view']);

// Authentication
$app->get('/auth/login', 'controllers/auth:loginPage')->setName('auth/login')->add($middleware['view']);
$app->post('/auth/login', 'controllers/auth:loginProcess');
$app->any('/auth/logout', 'controllers/auth:logoutProcess');

// Index
$app->get('/app', 'controllers/app:index')->setName('app/index')->add($middleware['view']);

// API routes
$app->group('/api', function () use ($app, $middleware) {
    $app->get('/balance[/{accountUID}[/{date}]]', 'controllers/app:getBalance');
    $app->get('/account/{UID}', 'controllers/app:getAccount');
    $app->get('/accounts', 'controllers/app:getAccounts');
    $app->get('/transaction/{UID}', 'controllers/app:getTransaction');
    $app->get('/transactions/tagged/{tags}', 'controllers/app:getTransactionsTagged');
    $app->get('/transactions[/{accountUID}[/{year}[/{month}[/{date}]]]]', 'controllers/app:getTransactions');

    $app->get('/stats/expense[/{year}[/{month}[/{date}]]]', 'controllers/app:getExpense');
    $app->get('/stats/income[/{year}[/{month}[/{date}]]]', 'controllers/app:getIncome');

    $app->post('/accounts', 'controllers/app:postNewAccount')->add($middleware['csrfp']);
    $app->put('/account/{UID}', 'controllers/app:putAccount')->add($middleware['csrfp']);
    $app->delete('/account/{UID}', 'controllers/app:deleteAccount')->add($middleware['csrfp']);
    $app->post('/transactions', 'controllers/app:postNewTransaction')->add($middleware['csrfp']);
    $app->put('/transaction/{UID}', 'controllers/app:putTransaction')->add($middleware['csrfp']);
    $app->delete('/transaction/{UID}', 'controllers/app:deleteTransaction')->add($middleware['csrfp']);

    $app->post('/profile', 'controllers/app:editProfile')->add($middleware['csrfp']);
});

// User-related
$app->get('/user/details', 'controllers/auth:getDetails');

// Assets
$app->get('/assets/{type}/{item:.+}', 'controllers/assets:get')->add($middleware['cache']);
// Bower components
$app->get('/components/{item:.+}', 'controllers/assets:getBowerComponent')->add($middleware['cache']);
