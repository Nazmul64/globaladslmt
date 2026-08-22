<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentpaymentmethod extends Model
{
    protected $table = 'agentpaymentmethods';
     protected $fillable = [
        'method_name',
        'photo',
        'status',
        'method_number',
        'new_photo',
        'agent_id',
    ];
    public function paymentMethods()
{
    return $this->hasMany(Agentpaymentmethod::class, 'agent_id');
}

public function agent()
{
    return $this->belongsTo(User::class, 'agent_id');
}

public function agentPaymentMethods()
{
    return $this->hasMany(Agentpaymentmethod::class, 'agent_id', 'agent_id');
}

}
