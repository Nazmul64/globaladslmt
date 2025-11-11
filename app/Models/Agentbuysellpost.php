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
        'available_balance',
        'duration',
        'payment_name',
        'status',
        'category_id',
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

}
