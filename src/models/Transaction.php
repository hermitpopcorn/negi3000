<?php
namespace App\Model;

class Transaction extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'transactions';

    public function account()
    {
        return $this->belongsTo('\App\Model\Account');
    }

    public function target()
    {
        return $this->belongsTo('\App\Model\Account', 'target_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Model\Tag', 'transactions_tags', 'transaction_id', 'tag_id')
            ->withPivot(['user_id'])
            ->where('transactions_tags.transaction_id', '=', $this->id)
        ;
    }

    public function getByUID($UID)
    {
        $transaction = $this->where('uid', $UID)->first();
        if(!$transaction) {
            return false;
        }
        return $transaction;
    }

    public function doInsert($UIDGenerator, $userID, $accountID, $targetID, $type, $amount, $note, $date)
    {
        $new = new self();

        $uidCheck = true;
        while($uidCheck) {
            $uid = $UIDGenerator();
            $uidCheck = $this->where('uid', $uid)->first();
        }

        $new->uid = $uid;
        $new->account_id = $accountID;
        $new->target_id = $targetID;
        $new->type = $type;
        $new->amount = $amount;
        $new->note = $note;
        $new->date = $date;
        $save = $new->save();

        if(!$save) {
            return false;
        }

        return $new;
    }

    public function doUpdate($transactionUID, $userID, $accountID, $targetID, $type, $amount, $note, $date)
    {
        $transaction = $this->where('uid', $transactionUID)->first();
        if(!$transaction) { return false; }

        if($transaction->account->user_id !== $userID) { return false; }

        $transaction->account_id = $accountID;
        $transaction->target_id = $targetID;
        $transaction->type = $type;
        $transaction->amount = $amount;
        $transaction->note = $note;
        $transaction->date = $date;
        $save = $transaction->save();

        if(!$save) {
            return false;
        }

        return $transaction;
    }

    public function delete()
    {
        $this->clearTags();
        return parent::delete();
    }

    public function getTags()
    {
        if(!isset($this->id)) {
            return false;
        }

        $tags = $this->tags()->get();
        $flatTags = [];
        foreach($tags as $tag) {
            $flatTags[] = $tag->name;
        }
        return $flatTags;
    }

    public function setTags($tagIDs)
    {
        if(!isset($this->id)) {
            return false;
        }

        foreach($tagIDs as $tagID) {
            $this->tags()->attach($tagID, ['user_id' => $this->account->user_id]);
        }

        return true;
    }

    public function clearTags()
    {
        if(!isset($this->id)) {
            return false;
        }

        return $this->tags()->detach();
    }
}
