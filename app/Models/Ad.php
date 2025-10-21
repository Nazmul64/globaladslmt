<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
   protected $fillable =[
       'code',
       'show_mrce_ads',
       'show_button_timer_ads',
       'show_banner_ads',
   ];
}
