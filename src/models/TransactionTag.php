<?php
namespace App\Model;

class TransactionTag extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'transactions_tags';

    public function transaction()
    {
        return $this->hasOne('\App\Model\Transaction', 'id', 'transaction_id');
    }

    public function tag()
    {
        return $this->hasOne('\App\Model\Tag', 'id', 'tag_id');
    }

    public function getTagged($userID, $tags)
    {
        $transactions = [];

        if(count($tags) == 1) {
            $transactionsTags = $this->where('user_id', $userID)->where('tag_id', $tags)->groupBy('transaction_id')->get();
            foreach($transactionsTags as $transactionTag) {
                $transactions[] = $transactionTag->transaction;
            }
        } else
        if(count($tags) > 1) {
            $transactionsTags = $this->where('user_id', $userID)->whereIn('tag_id', $tags)->groupBy('transaction_id')->get();
            foreach($transactionsTags as $transactionTag) {
                $tags2 = [];
                foreach($transactionTag->where('transaction_id', $transactionTag->transaction_id)->select('tag_id')->get() as $i) {
                    $tags2[] = $i->tag_id;
                }
                if(count($tags) == count(array_intersect($tags, $tags2))) {
                    $transactions[] = $transactionTag->transaction;
                }
            }
        }

        usort($transactions, function($a, $b) {
            if($a->date == $b->date) { return 0; }
            else if($a->date < $b->date) { return 1; }
            else if($a->date > $b->date) { return -1; }
        });

        return $transactions;
    }
}
