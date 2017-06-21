<?php
// Routes

// Home
$app->get('/', 'controllers/app:home')->setName('home')->add($middleware['view']);

// Authentication
$app->get('/auth/login', 'controllers/auth:loginPage')->setName('auth/login')->add($middleware['view']);
$app->post('/auth/login', 'controllers/auth:loginProcess');
$app->any('/auth/logout', 'controllers/auth:logoutProcess');

// App page
$app->get('/app', 'controllers/app:app')->setName('app/index')->add($middleware['view']);

// API routes
$app->group('/api', function () use ($app, $middleware) {
    // Accounts
    $app->get('/account/{UID}', 'controllers/api/accounts:getOne');
    $app->get('/accounts', 'controllers/api/accounts:getAll');
    $app->post('/accounts', 'controllers/api/accounts:post');
    $app->put('/account/{UID}', 'controllers/api/accounts:put');
    $app->delete('/account/{UID}', 'controllers/api/accounts:delete');

    // Transactions
    $app->get('/transaction/{UID}', 'controllers/api/transactions:getOne');
    $app->get('/transactions/tagged/{tags}', 'controllers/api/transactions:getSomeTagged');
    $app->get('/transactions[/{accountUID}[/{year}[/{month}[/{date}]]]]', 'controllers/api/transactions:getSome');
    $app->post('/transactions', 'controllers/api/transactions:post');
    $app->put('/transaction/{UID}', 'controllers/api/transactions:put');
    $app->delete('/transaction/{UID}', 'controllers/api/transactions:delete');

    // Stats
    $app->get('/balance[/{accountUID}[/{date}]]', 'controllers/api/stats:getBalance');
    $app->get('/stats/balance[/{accountUID}[/{date}]]', 'controllers/api/stats:getBalance');
    $app->get('/stats/expense[/{year}[/{month}[/{date}]]]', 'controllers/api/stats:getExpense');
    $app->get('/stats/income[/{year}[/{month}[/{date}]]]', 'controllers/api/stats:getIncome');

    // User-related
    $app->get('/user/details', 'controllers/api/users:getDetails');
    $app->patch('/profile', 'controllers/api/users:patchProfile');
})->add($middleware['auth-token']);

// Assets
$app->get('/assets/{type}/{item:.+}', 'controllers/assets:get')->add($middleware['cache']);
// Bower components
$app->get('/components/{item:.+}', 'controllers/assets:getBowerComponent')->add($middleware['cache']);
