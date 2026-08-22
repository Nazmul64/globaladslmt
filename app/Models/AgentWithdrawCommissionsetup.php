<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWithdrawCommissionsetup extends Model
{
    protected $fillable = ['max_widthraw','min_widthraw','widthraw_charge',];
    public function agent()
{
    return $this->belongsTo(User::class, 'agent_id');
}

}

