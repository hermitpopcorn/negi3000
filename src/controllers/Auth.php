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

        // Log in user by storing the user ID and CSRF token into session
        $segment = $this->session->getSegment('negi3000\Auth');
        $segment->set('user', $user->id);
        $segment->set('csrf_token', $this->generateCsrfToken());

        // Return success
        return $response->withStatus(200);
    }

    public function logoutProcess($request, $response, $args)
    {
        $this->session->destroy();

        return $response->withRedirect($this->container->get('router')->pathFor('home'));
    }

    public function getDetails($request, $response, $args)
    {
        // Get logged in user ID from session
        $segment = $this->session->getSegment('negi3000\Auth');
        $userID = $segment->get('user');

        // Fail if not logged in
        if(!$userID) {
            return $response->withStatus(403)->withJSON([
                'message' => "Invalid login."
            ]);
        }

        // Get user data from database
        $usersModel = $this->get('models/users');
        $user = $usersModel->find($userID);
        if(!$user) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid login"
            ]);
        }
        $userDetails = $user->getDetails();

        // Get CSRF token from session, and if not exits, generate a new one
        $csrfToken = $segment->get('csrf_token');
        if(!$csrfToken) {
            $csrfToken = $this->generateCsrfToken();
            $segment->set('csrf_token', $csrfToken);
        }
        $userDetails->csrfToken = $csrfToken;

        // Return user data
        return $response->withStatus(200)->withJSON($userDetails);
    }
}
