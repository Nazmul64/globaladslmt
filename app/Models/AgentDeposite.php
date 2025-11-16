<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentDeposite extends Model
{
     protected $fillable = [
        'agent_id',
        'amount',
        'transaction_id',
        'sender_account',
        'status',
        'new_photo',
        'photo',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }
       public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

}
