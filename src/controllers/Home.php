<?php
// Front-end Home controller

namespace App\Controller;

class Home extends \App\Controller\BaseController
{
    public function index($request, $response, $args)
    {
        // Render index view
        return $this->renderer->render($response, 'index.phtml', $args);
    }
}
