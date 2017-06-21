<?php
// Accounts API controller

namespace App\Controller\API;

class Accounts extends \App\Controller\BaseController
{
    public function getAll($request, $response, $args)
    {
        $usersModel = $this->get('models/users');
        $user = $usersModel->find($request->getAttribute('user'));
        if(!$user) {
            return $response->withStatus(404)->withJSON([
                'message' => "User not found."
            ]);
        }

        $accounts = $user->accounts;

        // Remove non-public columns
        foreach($accounts as &$account) {
            unset($account->id);
            unset($account->user_id);
            unset($account->created_at);
            unset($account->updated_at);
            $account->initialBalance = $account->initial_balance;
            unset($account->initial_balance);
            $account->isSink = (boolean) $account->is_sink;
            unset($account->is_sink);
        }
        unset($account);

        return $response->withJSON(['accounts' => $accounts]);
    }

    public function getOne($request, $response, $args)
    {
        $accountUID = $args['UID'];

        $accountsModel = $this->get('models/accounts');
        $account = $accountsModel->getByUID($accountUID);
        if(!$account || $account->user_id !== $request->getAttribute('user')) {
            return $response->withStatus(404)->withJSON([
                'message' => "Account not found."
            ]);
        }

        // Remove non-public columns
        unset($account->id);
        unset($account->user_id);
        unset($account->created_at);
        unset($account->updated_at);
        $account->initialBalance = $account->initial_balance;
        unset($account->initial_balance);
        $account->isSink = (boolean) $account->is_sink;
        unset($account->is_sink);

        return $response->withJSON(['account' => $account]);
    }

    public function post($request, $response, $args)
    {
        $userID = $request->getAttribute('user');
        $name = $request->getParam('name');
        $initialBalance = $request->getParam('initialBalance');
        $isSink = $request->getParam('isSink');

        $insert = false;

        // Verify user and accountAccount
        $accountsModel = $this->get('models/accounts');
        $UIDGenerator = function() { return $this->get('rsgen/identifier'); };
        $newAccount = $accountsModel->doInsert($UIDGenerator, $userID, $name, $initialBalance, (boolean) $isSink);
        if($newAccount) {
            $insert = $newAccount->uid;
        }

        if(!$insert) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid input."
            ]);
        }

        return $response->withStatus(200)->withJSON([
            'account' => $insert
        ]);
    }

    public function put($request, $response, $args)
    {
        $accountUID = $args['UID'];
        $userID = $request->getAttribute('user');
        $name = $request->getParam('name');
        $initialBalance = $request->getParam('initialBalance');
        $isSink = $request->getParam('isSink');

        $update = false;

        // Verify user and account
        $accountsModel = $this->get('models/accounts');
        $check = $accountsModel->check($userID, $accountUID);
        if($check) {
            $account = $accountsModel->doUpdate($accountUID, $userID, $name, $initialBalance, (boolean) $isSink);
            if($account) {
                $update = $account->uid;
            }
        }

        if(!$update) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid input."
            ]);
        }

        return $response->withStatus(200)->withJSON([
            'account' => $update
        ]);
    }

    public function delete($request, $response, $args)
    {
        $accountUID = $args['UID'];

        $delete = false;

        $accountsModel = $this->get('models/accounts');
        $account = $accountsModel->getByUID($accountUID);
        if(!$account) {
            return $response->withStatus(404)->withJSON([
                'message' => "Account not found."
            ]);
        }

        $delete = $account->delete();

        if(!$delete) {
            return $response->withStatus(400)->withJSON([
                'message' => "Deletion failed."
            ]);
        }

        return $response->withStatus(200);
    }
}
