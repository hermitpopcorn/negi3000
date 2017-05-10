<?php
namespace App\Model;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    public function accounts()
    {
        return $this->hasMany('\App\Model\Account');
    }

    public function transactions()
    {
        return $this->hasManyThrough('App\Model\Transaction', 'App\Model\Account');
    }

    public function authenticate($username, $password)
    {
        $user = $this
            ->select('id', 'password')
            ->where('username', $username)
            ->first()
        ;

        if(!$user) {
            return false;
        }

        $passwordCheck = password_verify($password, $user->password);

        if(!$passwordCheck) {
            return false;
        }

        return $user;
    }

    public function getDetails()
    {
        if(!isset($this->id)) {
            return false;
        }

        return (object) [
            'name' => $this->name,
            'username' => $this->username
        ];
    }

    public function getTransactions($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $query = $this->transactions();
        if($year && !$month) {
            $query = $query->whereBetween('date', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"]);
        } else
        if($year && $month && !$date) {
            $query = $query->whereBetween('date', ["{$year}-{$month}-01 00:00:00", date("Y-m-t 23:59:59", strtotime("{$year}-{$month}-01"))]);
        } else
        if($year && $month && $date) {
            $query = $query->whereBetween('date', ["{$year}-{$month}-{$date} 00:00:00", "{$year}-{$month}-{$date} 23:59:59"]);
        }
        $query->orderBy('date', 'DESC');

        return $query->get();
    }

    public function getTotalBalance($uptilDate = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $balance = 0;
        $accounts = $this->accounts;
        foreach($accounts as $account) {
            if(!$account->is_sink) {
                $balance += $account->getBalance($uptilDate);
            }
        }
        return $balance;
    }

    public function getIncome($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $balance = 0;
        $accounts = $this->accounts;
        foreach($accounts as $account) {
            $balance += $account->getIncome($year, $month, $date);
        }
        return $balance;
    }

    public function getExpense($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $balance = 0;
        $accounts = $this->accounts;
        foreach($accounts as $account) {
            $balance += $account->getExpense($year, $month, $date);
        }
        return $balance;
    }
}
