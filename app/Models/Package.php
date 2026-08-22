<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = [
        'package_name',
        'price',
        'photo',
        'daily_income',
        'daily_limit',
        'ad_brack',
        'validity',
        'new_photo',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'daily_income' => 'decimal:2',
        'daily_limit' => 'integer',
        'ad_brack' => 'integer',
        'validity' => 'string',
    ];
}
