<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stepguide extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'serial_number',
    ];
}
