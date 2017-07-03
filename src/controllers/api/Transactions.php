<?php
// Transactions API controller

namespace App\Controller\API;

class Transactions extends \App\Controller\BaseController
{
    public function getOne($request, $response, $args)
    {
        $transactionUID = $args['UID'];

        $transactionsModel = $this->get('models/transactions');
        $transaction = $transactionsModel->getByUID($transactionUID);

        if(!$transaction) {
            return $response->withStatus(404)->withJSON([
                'message' => "Transaction not found."
            ]);
        }

        if($transaction->account->user_id !== $request->getAttribute('user')) {
            return $response->withStatus(403)->withJSON([
                'message' => "Transaction not yours."
            ]);
        }

        $accUID = $transaction->account->uid;
        unset($transaction->account);
        unset($transaction->account_id);
        $transaction->account = $accUID;
        $transaction->tags = $transaction->getTags();
        if($transaction->target) {
            $targetUID = $transaction->target->uid;
            unset($transaction->target);
            $transaction->target = $targetUID;
        }
        unset($transaction->target_id);
        unset($transaction->id);
        unset($transaction->created_at);
        unset($transaction->updated_at);

        return $response->withJSON(['transaction' => $transaction]);
    }

    public function getSome($request, $response, $args)
    {
        $accountUID = !empty($args['accountUID']) ? $args['accountUID'] : 'all';
        $year = !empty($args['year']) ? $args['year'] : false;
        $month = !empty($args['month']) ? $args['month'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        if($accountUID !== 'all') {
            $accountsModel = $this->get('models/accounts');
            $account = $accountsModel->getByUID($accountUID);
            if(!$account) {
                return $response->withStatus(400)->withJSON([
                    'message' => "Account not found."
                ]);
            }
            if($account->user_id !== $request->getAttribute('user')) {
                return $response->withStatus(403)->withJSON([
                    'message' => "Account not yours."
                ]);
            }

            $transactions = $account->getTransactions($year, $month, $date);
        } else {
            $usersModel = $this->get('models/users');
            $user = $usersModel->find($request->getAttribute('user'));
            if(!$user) {
                return $response->withStatus(400)->withJSON([
                    'message' => "User not found."
                ]);
            }
            $transactions = $user->getTransactions($year, $month, $date);
        }

        // Get tags and remove IDs
        foreach($transactions as &$transaction) {
            $transaction->tags = $transaction->getTags();
            unset($transaction->id);
            unset($transaction->user_id);
            $accountUID = $transaction->account->uid;
            $transaction->accountName = $transaction->account->name;
            $transaction->accountIsSink = (boolean) $transaction->account->is_sink;
            unset($transaction->account);
            $transaction->account = $accountUID;
            if($transaction->target) {
                $targetUID = $transaction->target->uid;
                $transaction->targetName = $transaction->target->name;
                $transaction->targetIsSink = (boolean) $transaction->target->is_sink;
                unset($transaction->target);
                $transaction->target = $targetUID;
            }
            unset($transaction->target_id);
            unset($transaction->account_id);
            unset($transaction->created_at);
            unset($transaction->updated_at);
        }
        unset($transaction);

        return $response->withJSON(['transactions' => $transactions]);
    }

    public function getSomeTagged($request, $response, $args)
    {
        $userID = $request->getAttribute('user');
        $tags = array_filter(explode(',', $args['tags']));

        $tagsModel = $this->get('models/tags');
        $tagIDs = [];
        foreach($tags as $tag) {
            $tagID = $tagsModel->where('name', $tag)->first();
            if($tagID) {
                $tagIDs[] = $tagID->id;
            }
        }

        $transactionsTagsModel = $this->get('models/transactions-tags');
        $transactions = $transactionsTagsModel->getTagged($userID, $tagIDs);

        // Get tags and remove IDs
        foreach($transactions as &$transaction) {
            $transaction->tags = $transaction->getTags();
            unset($transaction->id);
            unset($transaction->user_id);
            $accountUID = $transaction->account->uid;
            $transaction->accountName = $transaction->account->name;
            $transaction->accountIsSink = (boolean) $transaction->account->is_sink;
            unset($transaction->account);
            $transaction->account = $accountUID;
            if($transaction->target) {
                $targetUID = $transaction->target->uid;
                $transaction->targetName = $transaction->target->name;
                $transaction->targetIsSink = (boolean) $transaction->target->is_sink;
                unset($transaction->target);
                $transaction->target = $targetUID;
            }
            unset($transaction->target_id);
            unset($transaction->account_id);
            unset($transaction->created_at);
            unset($transaction->updated_at);
        }
        unset($transaction);

        return $response->withJSON(['transactions' => $transactions]);
    }

    public function post($request, $response, $args)
    {
        $userID = $request->getAttribute('user');
        $type = $request->getParam('type');
        $accountUID = $request->getParam('account');
        $targetUID = $type === 'x' ? $request->getParam('target') : null;
        $amount = $request->getParam('amount');
        $note = $request->getParam('note');
        $date = $request->getParam('date');
        $tags = $request->getParam('tags');

        $insert = false;

        // Make sure the date given is valid
        if(strtotime($date) === false) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid date."
            ]);
        }

        // Verify user and account
        $accountsModel = $this->get('models/accounts');
        $accountID = $accountsModel->check($userID, $accountUID);
        $targetID = true;
        if($targetUID) {
            $targetID = $accountsModel->check($userID, $targetUID);
        }
        if($accountID && $targetID) {
            $transactionsModel = $this->get('models/transactions');
            $UIDGenerator = function() { return $this->get('rsgen/identifier'); };
            $newTransaction = $transactionsModel->doInsert($UIDGenerator, $userID, $accountID, $targetID !== true ? $targetID : null, $type, $amount, $note, $date);
            if($newTransaction) {
                $tagsModel = $this->get('models/tags');
                $tagIDs = [];
                foreach($tags as $tag) {
                    $tagID = $tagsModel->insert($tag);
                    if($tagID) { $tagIDs[] = $tagID; }
                }

                $newTransaction->setTags($tagIDs);

                $insert = $newTransaction->uid;
            }
        }

        if(!$insert) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid input."
            ]);
        }

        return $response->withStatus(200)->withJSON([
            'transaction' => $insert
        ]);
    }

    public function put($request, $response, $args)
    {
        $transactionUID = $args['UID'];
        $userID = $request->getAttribute('user');
        $type = $request->getParam('type');
        $accountUID = $request->getParam('account');
        $targetUID = $type === 'x' ? $request->getParam('target') : null;
        $amount = $request->getParam('amount');
        $note = $request->getParam('note');
        $date = $request->getParam('date');
        $tags = $request->getParam('tags');

        $update = false;

        // Make sure the date given is valid
        if(strtotime($date) === false) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid date."
            ]);
        }

        // Verify user and account
        $accountsModel = $this->get('models/accounts');
        $accountID = $accountsModel->check($userID, $accountUID);
        $targetID = true;
        if($targetUID) {
            $targetID = $accountsModel->check($userID, $targetUID);
        }
        if($accountID && $targetID) {
            $transactionsModel = $this->get('models/transactions');
            $transaction = $transactionsModel->doUpdate($transactionUID, $userID, $accountID, $targetID !== true ? $targetID : null, $type, $amount, $note, $date);
            if($transaction) {
                $tagsModel = $this->get('models/tags');
                $tagIDs = [];
                foreach($tags as $tag) {
                    $tagID = $tagsModel->insert($tag);
                    if($tagID) { $tagIDs[] = $tagID; }
                }

                $transaction->clearTags();
                $transaction->setTags($tagIDs);

                $update = $transaction->uid;
            }
        }

        if(!$update) {
            return $response->withStatus(400)->withJSON([
                'message' => "Invalid input."
            ]);
        }

        return $response->withStatus(200)->withJSON([
            'transaction' => $update
        ]);
    }

    public function delete($request, $response, $args)
    {
        $transactionUID = $args['UID'];

        $delete = false;

        $transactionsModel = $this->get('models/transactions');
        $transaction = $transactionsModel->getByUID($transactionUID);
        if(!$transaction) {
            return $response->withStatus(404)->withJSON([
                'message' => "Transaction not found."
            ]);
        }

        $delete = $transaction->delete();

        if(!$delete) {
            return $response->withStatus(400)->withJSON([
                'message' => "Deletion failed."
            ]);
        }

        return $response->withStatus(200);
    }
}
