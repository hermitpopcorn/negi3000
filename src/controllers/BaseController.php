<?php
// Base Controller

namespace App\Controller;

class BaseController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __get($name)
    {
        return $this->container->get($name);
    }

    public function __call($name, $args)
    {
        if($name == "get" && count($args) == 1) {
            return $this->container->get($args[0]);
        } else {
            $callable = $this->container->get($name);
            return call_user_func_array($callable, $args);
        }
    }

    protected function generateToken()
    {
        return $this->container->get('rsgen/token');
    }
}
