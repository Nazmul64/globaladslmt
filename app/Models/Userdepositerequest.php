<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdepositerequest extends Model
{
    use HasFactory;

    // ✅ Specify correct table name (শেষে s আছে)
    protected $table = 'userdepositerequests';

    protected $fillable = [
        'user_id',
        'agent_id',
        'post_id',           // ✅ এইটা আছে
        'amount',
        'status',
        'transaction_id',
        'sender_account',
        'photo',
        'agent_commission',
        'admin_commission',
        'type',
        'orderrelasce',
    ];

    protected $casts = [
        'amount' => 'float',
        'agent_commission' => 'float',
        'admin_commission' => 'float',
    ];

    // ❌ IMPORTANT: NO boot() method!
    // ❌ NO Event that updates User balance!

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Agent relationship
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Post relationship (Agent buy/sell post)
     */
    public function post()
    {
        return $this->belongsTo(Agentbuysellpost::class, 'post_id');
    }

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
