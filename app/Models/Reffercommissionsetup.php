<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reffercommissionsetup extends Model
{
    protected $fillable = [
        'reffer_level',
        'commission_percentage',
    ];
}
