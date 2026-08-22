<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentwidthraw extends Model
{
      protected $fillable = [
        'payment_method_id',
        'account_number',
        'wallet_address',
        'user_id',
        'amount',
        'status',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Paymentmethod::class, 'payment_method_id');
    }

}
