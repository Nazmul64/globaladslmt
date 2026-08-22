<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockedSetting extends Model
{
    use HasFactory;

    protected $table = 'locked_settings';

    protected $fillable = [
        'locked_amount',  // ✅ Match with migration
        'status'
    ];

    protected $casts = [
        'locked_amount' => 'decimal:2',
        'status' => 'boolean'
    ];

    /**
     * Get active lock amount
     */
    public static function getActiveLockAmount()
    {
        $setting = self::where('status', 1)->first();
        return $setting ? $setting->locked_amount : 0;
    }
}
