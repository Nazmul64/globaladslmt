<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depositelimite extends Model
{
       protected $fillable = ['max_deposit','min_deposit'];
}
