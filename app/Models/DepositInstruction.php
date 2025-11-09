<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositInstruction extends Model
{
     protected $fillable = [
        'video_url',
        'member_ship_instructions_title',
        'member_ship_instructions_description',
        'deposite_instructions_title',
        'deposite_instructions_description',
    ];
}
