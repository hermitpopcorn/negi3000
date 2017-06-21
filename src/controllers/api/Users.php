<?php
// User-related API controller

namespace App\Controller\API;

class Users extends \App\Controller\BaseController
{
    public function patchProfile($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
        $userID = $segment->get('user');

        $name = $request->getParam('name');
        $password = $request->getParam('password');

        $update = false;

        if(!$name && !$password) {
            return $response->withStatus(400)->withJSON([
                'message' => "User data unchanged."
            ]);
        }

        // Verify user and account
        $userModel = $this->get('models/users');
        $user = $userModel->find($userID);
        if(!$user) {
            return $response->withStatus(404)->withJSON([
                'message' => "User not found."
            ]);
        }

        if($name) {
            $user->name = $name;
            $update = $user->save();
        }
        if($password) {
            $user->password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
            $update = $user->save();
        }

        if(!$update) {
            return $response->withStatus(400)->withJSON([
                'message' => "Changes not saved."
            ]);
        }

        return $response->withStatus(200)->withJSON([
            'message' => "User data updated."
        ]);
    }

    public function getDetails($request, $response, $args)
    {
        $userID = $request->getAttribute('user');

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
                'message' => "Invalid login."
            ]);
        }
        $userDetails = $user->getDetails();

        // Return user data
        return $response->withStatus(200)->withJSON($userDetails);
    }
}
