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
    $app->post('/accounts', 'controllers/api/accounts:post')->add($middleware['csrfp']);
    $app->put('/account/{UID}', 'controllers/api/accounts:put')->add($middleware['csrfp']);
    $app->delete('/account/{UID}', 'controllers/api/accounts:delete')->add($middleware['csrfp']);

    // Transactions
    $app->get('/transaction/{UID}', 'controllers/api/transactions:getOne');
    $app->get('/transactions/tagged/{tags}', 'controllers/api/transactions:getSomeTagged');
    $app->get('/transactions[/{accountUID}[/{year}[/{month}[/{date}]]]]', 'controllers/api/transactions:getSome');
    $app->post('/transactions', 'controllers/api/transactions:post')->add($middleware['csrfp']);
    $app->put('/transaction/{UID}', 'controllers/api/transactions:put')->add($middleware['csrfp']);
    $app->delete('/transaction/{UID}', 'controllers/api/transactions:delete')->add($middleware['csrfp']);

    // Stats
    $app->get('/balance[/{accountUID}[/{date}]]', 'controllers/api/stats:getBalance');
    $app->get('/stats/balance[/{accountUID}[/{date}]]', 'controllers/api/stats:getBalance');
    $app->get('/stats/expense[/{year}[/{month}[/{date}]]]', 'controllers/api/stats:getExpense');
    $app->get('/stats/income[/{year}[/{month}[/{date}]]]', 'controllers/api/stats:getIncome');

    // User-related
    $app->patch('/profile', 'controllers/api/users:patchProfile')->add($middleware['csrfp']);
});

// User-related
$app->get('/user/details', 'controllers/auth:getDetails');

// Assets
$app->get('/assets/{type}/{item:.+}', 'controllers/assets:get')->add($middleware['cache']);
// Bower components
$app->get('/components/{item:.+}', 'controllers/assets:getBowerComponent')->add($middleware['cache']);
