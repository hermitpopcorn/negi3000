<?php
// Application main controller

namespace App\Controller;

class App extends \App\Controller\BaseController
{
    public function __construct($container)
    {
        parent::__construct($container);

        $segment = $this->session->getSegment('negi3000\Auth');
        if($segment->get('user')) {
            $this->segment = $segment;
        } else {
            $this->segment = false;
        }
    }

    public function home($request, $response, $args)
    {
        return $this->renderer->render($response, 'index.phtml', $args);
    }

    public function app($request, $response, $args)
    {
        return $this->renderer->render($response, 'app/index.phtml');
    }
}
