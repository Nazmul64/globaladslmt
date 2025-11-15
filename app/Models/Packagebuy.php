<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packagebuy extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'amount',
        'status',
        'daily_income',
        'daily_limit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

}
