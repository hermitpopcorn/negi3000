<?php
namespace App\Model;

class Account extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'accounts';

    public function user()
    {
        return $this->belongsTo('\App\Model\User');
    }

    public function transactions()
    {
        return $this->hasMany('\App\Model\Transaction');
    }

    public function transfers()
    {
        return $this->hasMany('\App\Model\Transaction', 'target_id')->where('type', 'x');
    }

    public function getByUID($UID)
    {
        $account = $this->where('uid', $UID)->first();
        if(!$account) {
            return false;
        }
        return $account;
    }

    // returns account ID
    public function check($userID, $accountUID)
    {
        $acc = $this->where('uid', $accountUID)->first();
        if(!$acc) { return false; }
        if($acc->user_id !== $userID) { return false; }
        return $acc->id;
    }

    public function doInsert($UIDGenerator, $userID, $name, $initialBalance, $isSink)
    {
        $new = new self();

        $uidCheck = true;
        while($uidCheck) {
            $uid = $UIDGenerator();
            $uidCheck = $this->where('uid', $uid)->first();
        }

        $new->uid = $uid;
        $new->user_id = $userID;
        $new->name = $name;
        $new->initial_balance = $initialBalance;
        $new->is_sink = $isSink;
        $save = $new->save();

        if(!$save) {
            return false;
        }

        return $new;
    }

    public function doUpdate($accountUID, $userID, $name, $initialBalance, $isSink)
    {
        $account = $this->where('uid', $accountUID)->first();
        if(!$account) { return false; }

        $account->user_id = $userID;
        $account->name = $name;
        $account->initial_balance = $initialBalance;
        $account->is_sink = $isSink;
        $save = $account->save();

        if(!$save) {
            return false;
        }

        return $account;
    }

    public function delete()
    {
        $this->transactions()->delete();
        return parent::delete();
    }

    public function getBalance($uptilDate = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        if($uptilDate) {
            $split = explode(" ", $uptilDate);
            // if time is not specified
            if(count($split) < 2) {
                $uptilDate = "{$uptilDate} 23:59:59";
            }
        }

        // Calculate transactions
        $balance = $this->initial_balance;
        if(!$uptilDate) {
            $transactions = $this->transactions;
        } else {
            $transactions = $this->transactions()->where('date', '<=', $uptilDate)->get();
        }
        foreach($transactions as $transaction) {
            if($transaction->type === 'i') {
                $balance += $transaction->amount;
            } else
            if($transaction->type === 'e' || $transaction->type === 'x') {
                $balance -= $transaction->amount;
            }
        }

        // Calculate transfers
        if(!$uptilDate) {
            $transfers = $this->transfers;
        } else {
            $transfers = $this->transfers()->where('date', '<=', $uptilDate)->get();
        }
        foreach($transfers as $transfer) {
            $balance += $transfer->amount;
        }
        return $balance;
    }

    public function queryTransactionsByDate($year = false, $month = false, $date = false)
    {
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

        return $query;
    }

    public function getTransactions($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        return $this->queryTransactionsByDate($year, $month, $date)->get();
    }

    public function getIncomeTransactions($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        return $this->queryTransactionsByDate($year, $month, $date)
            ->where(function($q) {
                return $q
                    ->where('type', 'i')
                    ->orWhere(function($q) {
                        return $q
                            ->where('type', 'x')
                            ->whereHas('account', function($query) {
                            $query->where('is_sink', 1);
                        });
                    })
                ;
            })
            ->get()
        ;
    }

    public function getExpenseTransactions($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        return $this->queryTransactionsByDate($year, $month, $date)
            ->where(function($q) {
                return $q
                    ->where('type', 'e')
                    ->orWhere(function($q) {
                        return $q
                            ->where('type', 'x')
                            ->whereHas('target', function($query) {
                            $query->where('is_sink', 1);
                        });
                    })
                ;
            })
            ->get()
        ;
    }

    public function getIncome($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $total = 0;
        $transactions = $this->getIncomeTransactions($year, $month, $date);
        foreach($transactions as $transaction) {
            $total += $transaction->amount;
        }
        return $total;
    }

    public function getExpense($year = false, $month = false, $date = false)
    {
        if(!isset($this->id)) {
            return false;
        }

        $total = 0;
        $transactions = $this->getExpenseTransactions($year, $month, $date);
        foreach($transactions as $transaction) {
            $total += $transaction->amount;
        }
        return $total;
    }
}
