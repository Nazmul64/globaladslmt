<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widthrawlimit extends Model
{
   protected $fillable =[
      'max_withdraw_limit',
      'min_withdraw_limit',
   ];
}
