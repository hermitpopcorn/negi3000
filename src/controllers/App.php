<?php
// Application main controller

namespace App\Controller;

class App extends \App\Controller\BaseController
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function home($request, $response, $args)
    {
        return $this->renderer->render($response, 'index.phtml', $args);
    }

    public function app($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');

        return $this->renderer->render($response, 'app/index.phtml', [ 'token' => $segment->get('token') ]);
    }
}
