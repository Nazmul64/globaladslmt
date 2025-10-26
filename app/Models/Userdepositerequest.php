<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userdepositerequest extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'amount',
        'status',
        'transaction_id',
        'sender_account',
        'photo',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function agent(){
        return $this->belongsTo(User::class, 'agent_id');
    }
}
