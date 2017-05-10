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

    public function index($request, $response, $args)
    {
        return $this->renderer->render($response, 'app/index.phtml');
    }

    public function getBalance($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $accountUID = !empty($args['accountUID']) ? $args['accountUID'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        if(!$accountUID || $accountUID == 'all') {
            $usersModel = $this->get('models/users');
            $user = $usersModel->find($this->segment->get('user'));
            if(!$user) {
                return $response->withStatus(404)->withJSON([
                    'message' => "User not found."
                ]);
            }
            $balance = $user->getTotalBalance($date);
        } else {
            $accountsModel = $this->get('models/accounts');
            $accountID = $accountsModel->check($this->segment->get('user'), $accountUID);
            if(!$accountUID) {
                return $response->withStatus(404)->withJSON([
                    'message' => "Account not found."
                ]);
            }
            $account = $accountsModel->getByUID($accountUID);
            $balance = $account->getBalance($date);
        }

        return $response->withJSON(['balance' => $balance]);
    }

    public function getAccounts($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $usersModel = $this->get('models/users');
        $user = $usersModel->find($this->segment->get('user'));
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

    public function getAccount($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $accountUID = $args['UID'];

        $accountsModel = $this->get('models/accounts');
        $account = $accountsModel->getByUID($accountUID);
        if(!$account || $account->user_id !== $this->segment->get('user')) {
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

    public function getIncome($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $year = !empty($args['year']) ? $args['year'] : false;
        $month = !empty($args['month']) ? $args['month'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        $usersModel = $this->get('models/users');
        $user = $usersModel->find($this->segment->get('user'));
        if(!$user) {
            return $response->withStatus(400)->withJSON([
                'message' => "User not found."
            ]);
        }

        $income = $user->getIncome($year, $month, $date);

        return $response->withJSON(['income' => $income]);
    }

    public function getExpense($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $year = !empty($args['year']) ? $args['year'] : false;
        $month = !empty($args['month']) ? $args['month'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        $usersModel = $this->get('models/users');
        $user = $usersModel->find($this->segment->get('user'));
        if(!$user) {
            return $response->withStatus(400)->withJSON([
                'message' => "User not found."
            ]);
        }

        $expense = $user->getExpense($year, $month, $date);

        return $response->withJSON(['expense' => $expense]);
    }

    public function getTransaction($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $transactionUID = $args['UID'];

        $transactionsModel = $this->get('models/transactions');
        $transaction = $transactionsModel->getByUID($transactionUID);

        if(!$transaction) {
            return $response->withStatus(404)->withJSON([
                'message' => "Transaction not found."
            ]);
        }

        if($transaction->account->user_id !== $this->segment->get('user')) {
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

    public function getTransactions($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

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
            if($account->user_id !== $this->segment->get('user')) {
                return $response->withStatus(403)->withJSON([
                    'message' => "Account not yours."
                ]);
            }

            $transactions = $account->getTransactions($year, $month, $date);
        } else {
            $usersModel = $this->get('models/users');
            $user = $usersModel->find($this->segment->get('user'));
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
            $transaction->isSink = (boolean) $transaction->account->is_sink;
            unset($transaction->account);
            $transaction->account = $accountUID;
            if($transaction->target) {
                $targetUID = $transaction->target->uid;
                $transaction->targetName = $transaction->target->name;
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

    public function getTransactionsTagged($request, $response, $args)
    {
        if(!$this->segment) {
            return $this->forbiddenHandler($request, $response);
        }

        $segment = $this->session->getSegment('negi3000\Auth');
        $userID = $segment->get('user');
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
            $transaction->isSink = (boolean) $transaction->account->is_sink;
            unset($transaction->account);
            $transaction->account = $accountUID;
            if($transaction->target) {
                $targetUID = $transaction->target->uid;
                $transaction->targetName = $transaction->target->name;
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

    public function postNewAccount($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
        $userID = $segment->get('user');
        $name = $request->getParam('name');
        $initialBalance = $request->getParam('initialBalance');
        $isSink = $request->getParam('isSink');

        $insert = false;

        // Verify user and account
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

    public function putAccount($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
        $accountUID = $args['UID'];
        $userID = $segment->get('user');
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

    public function deleteAccount($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
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

    public function postNewTransaction($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
        $userID = $segment->get('user');
        $type = $request->getParam('type');
        $accountUID = $request->getParam('account');
        $targetUID = $type === 'x' ? $request->getParam('target') : null;
        $amount = $request->getParam('amount');
        $note = $request->getParam('note');
        $date = $request->getParam('date');
        $tags = $request->getParam('tags');

        $insert = false;

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

    public function putTransaction($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
        $transactionUID = $args['UID'];
        $userID = $segment->get('user');
        $type = $request->getParam('type');
        $accountUID = $request->getParam('account');
        $targetUID = $type === 'x' ? $request->getParam('target') : null;
        $amount = $request->getParam('amount');
        $note = $request->getParam('note');
        $date = $request->getParam('date');
        $tags = $request->getParam('tags');

        $update = false;

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

    public function deleteTransaction($request, $response, $args)
    {
        $segment = $this->session->getSegment('negi3000\Auth');
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

    public function editProfile($request, $response, $args)
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
}
