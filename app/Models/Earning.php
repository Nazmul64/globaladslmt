<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    protected $table = 'earnings';

    protected $fillable = [
        'user_id',
        'package_id',
        'earning_date',
        'daily_ad_limit',
        'ads_watched_today',
        'income_per_ad',
        'today_earning',
        'total_earning',
        'last_break_started',
        'last_reward_claimed',
        'last_claimed_cycle',
    ];

    protected $casts = [
        'earning_date' => 'date:Y-m-d',
        'daily_ad_limit' => 'integer',
        'ads_watched_today' => 'integer',
        'income_per_ad' => 'decimal:2',
        'today_earning' => 'decimal:2',
        'total_earning' => 'decimal:2',
        'last_break_started' => 'datetime',
    ];

    // relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }



}
