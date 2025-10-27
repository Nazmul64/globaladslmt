<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdepositerequest extends Model
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
        'type',
    ];

    // ইউজার যিনি ডিপোজিট করছেন
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // এজেন্ট যিনি রিকোয়েস্টটি হ্যান্ডেল করছেন
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
