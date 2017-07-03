<?php
namespace App\Model;

class Tag extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'tags';

    public function transactions()
    {
        return $this->belongsToMany('App\Model\Transaction')->using('App\Model\TransactionTag');
        return $this->belongsToMany('App\Model\Transaction', 'transactions_tags', 'tag_id', 'transaction_id')
            ->withPivot(['user_id'])
            ->where('transactions_tags.tag_id', '=', $this->id)
        ;
    }

    public function insert($tag)
    {
        $tag = preg_replace('/[, ]/i', "#", $tag);
        $tag = ltrim($tag, "#");

        if(strlen($tag) < 1) {
            return false;
        }

        $tag = explode("#", $tag);
        $tag = $tag[0];

        if(strlen($tag) < 1) {
            return false;
        }

        $existing = $this->where('name', $tag)->first();
        if($existing) {
            return $existing->id;
        } else {
            $new = new self();
            $new->name = $tag;
            $save = $new->save();
            if($save) {
                return $new->id;
            } else {
                return false;
            }
        }
    }
}
