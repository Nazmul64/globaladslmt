<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentpaymentmethod extends Model
{
     protected $fillable = [
        'method_name',
        'photo',
        'status',
        'method_number',
        'new_photo',
    ];
}
