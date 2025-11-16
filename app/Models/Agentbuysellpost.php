<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentbuysellpost extends Model
{
    protected $fillable = [
        'new_photo',
        'agent_id',
        'photo',
        'trade_limit',
        'trade_limit_two',
        'rate_balance',
        'payment_name',
        'status',
        'category_id',
        'dollarsigends_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'category_id');
    // }


        public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id', 'id');
        // যদি Agent আলাদা model হয়, তাহলে:
        // return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    // ✅ Category Relationship
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
public function agentamounts()
{
    return $this->hasMany(AgentDeposite::class, 'agent_id', 'agent_id');
}

public function agentamount()
{
    return $this->hasOne(AgentDeposite::class, 'agent_id', 'agent_id')->latestOfMany();
}



public function dollarsign()
{
    return $this->belongsTo(TakaandDollarsigend::class, 'dollarsigends_id');
}



}
