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

// CSRF protection
$middleware['csrfp'] = function ($request, $response, $next) use ($container) {
    $segment = $container->session->getSegment('negi3000\Auth');
    $sessionToken = $segment->get('csrf_token');
    $submittedToken = $request->getParam('csrfToken');
    if($sessionToken !== $submittedToken) {
        return $response->withStatus(400)->withJson([
            'message' => "Invalid CSRF token."
        ]);
    }
    return $next($request, $response);
};

$middleware['cache'] = new \Slim\HttpCache\Cache('public', 86400);
