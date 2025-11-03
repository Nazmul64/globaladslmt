<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
   protected $fillable = [
        'user_id',
        'package_buy_id',
        'coins_earned',
        'bonus_earned',
        'completed_at',
        'ip_address',
        'type',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'coins_earned' => 'decimal:2',
        'bonus_earned' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packageBuy()
    {
        return $this->belongsTo(PackageBuy::class);
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('completed_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('completed_at', now()->month)
                     ->whereYear('completed_at', now()->year);
    }
}
