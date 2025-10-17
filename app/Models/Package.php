<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'package_name',
        'price',
        'photo',
        'daily_income',
        'daily_limit',
        'new_photo',
    ];
}
