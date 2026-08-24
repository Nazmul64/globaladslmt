<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logosetting extends Model
{
    protected $table = 'logosettings';
    
    protected $fillable = [
        'photo',
    ];
}
