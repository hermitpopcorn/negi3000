<?php
// Base API Controller

namespace App\Controller\API;

class BaseController extends \App\Controller\BaseController
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
}
