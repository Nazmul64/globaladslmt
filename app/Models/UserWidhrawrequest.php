<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWidhrawrequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'amount',
        'status',
        'transaction_id',
        'sender_account',
        'photo',
        'agent_commission',
        'admin_commission',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
