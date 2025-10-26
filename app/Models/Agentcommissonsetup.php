<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentcommissonsetup extends Model
{
   protected $fillable = [
        'deposit_agent_commission',
        'withdraw_total_commission',
        'commission_type',
        'agent_share_percent',
        'admin_share_percent',
        'status',
    ];
}
