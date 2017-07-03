<?php
// Authentication controller

namespace App\Controller;

class Auth extends \App\Controller\BaseController
{
    public function loginPage($request, $response, $args)
    {
        // Check if logged in, and if so, redirect to App page
        $segment = $this->session->getSegment('negi3000\Auth');
        if($segment->get('user')) {
            $users = $this->container->get('models/users');
            $user = $users->find($segment->get('user'));
            if($user) {
                // If logged in, just refresh the token
                $segment->set('token', $user->token);

                return $response->withRedirect(
                    $this->router->pathFor('app/index')
                );
            }
        }

        // Render login page
        return $this->renderer->render($response, 'auth/login.phtml');
    }

    public function loginProcess($request, $response, $args)
    {
        // Get POST data
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        // Check username and password
        $users = $this->container->get('models/users');
        $user = $users->authenticate($username, $password);

        // Fail if no match
        if(!$user) {
            return $response->withStatus(400)->withJSON([
                'success' => false,
                'message' => "Invalid username and/or password."
            ]);
        }

        $tokenGenerator = function() { return $this->get('rsgen/token'); };
        $setToken = $user->setToken($tokenGenerator);
        if(!$setToken) {
            return $response->withStatus(500)->withJSON([
                'success' => false,
                'message' => "Unable to set token."
            ]);
        }

        // Log in user by storing the user ID and CSRF token into session
        $segment = $this->session->getSegment('negi3000\Auth');
        $segment->set('user', $user->id);
        $segment->set('token', $setToken);

        // Return success
        return $response->withStatus(200)->withJSON(['token' => $setToken]);
    }

    public function logoutProcess($request, $response, $args)
    {
        $this->session->destroy();

        return $response->withRedirect($this->container->get('router')->pathFor('home'));
    }
}
