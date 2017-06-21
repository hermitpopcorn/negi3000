<?php
// Application middleware

// Rendered view middleware
$middleware['view'] = function ($request, $response, $next) {
    $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost() . $request->getUri()->getBasePath() . "/";
    $renderer = $this->get('renderer');
    $renderer->addAttribute('baseUrl', $baseUrl);
    $request = $request->withAttribute('baseUrl', $baseUrl);
    return $next($request, $response);
};

// Require authorization token
$middleware['auth-token'] = function ($request, $response, $next) use ($container) {
    // Get token from authorization header
    $token = $request->getHeader('Authorization');

    $usersModel = $this->get('models/users');
    $user = $usersModel->findByToken($token);
    if(!$user) {
        return $response->withStatus(403)->withJSON([
            'message' => "Unauthorized"
        ]);
    }

    return $next($request->withAttribute('user', $user), $response);
};

$middleware['cache'] = new \Slim\HttpCache\Cache('public', 86400);
