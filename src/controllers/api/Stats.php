<?php
// Statistics API controller

namespace App\Controller\API;

class Stats extends \App\Controller\BaseController
{
    public function getBalance($request, $response, $args)
    {
        $accountUID = !empty($args['accountUID']) ? $args['accountUID'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        if(!$accountUID || $accountUID == 'all') {
            $usersModel = $this->get('models/users');
            $user = $usersModel->find($request->getAttribute('user'));
            if(!$user) {
                return $response->withStatus(404)->withJSON([
                    'message' => "User not found."
                ]);
            }
            $balance = $user->getTotalBalance($date);
        } else {
            $accountsModel = $this->get('models/accounts');
            $accountID = $accountsModel->check($request->getAttribute('user'), $accountUID);
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

    public function getIncome($request, $response, $args)
    {
        $year = !empty($args['year']) ? $args['year'] : false;
        $month = !empty($args['month']) ? $args['month'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        $usersModel = $this->get('models/users');
        $user = $usersModel->find($request->getAttribute('user'));
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
        $year = !empty($args['year']) ? $args['year'] : false;
        $month = !empty($args['month']) ? $args['month'] : false;
        $date = !empty($args['date']) ? $args['date'] : false;

        $usersModel = $this->get('models/users');
        $user = $usersModel->find($request->getAttribute('user'));
        if(!$user) {
            return $response->withStatus(400)->withJSON([
                'message' => "User not found."
            ]);
        }

        $expense = $user->getExpense($year, $month, $date);

        return $response->withJSON(['expense' => $expense]);
    }
}
