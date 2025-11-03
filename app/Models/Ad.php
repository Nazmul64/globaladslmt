<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
   protected $fillable = [
        'banner_ad_1',
        'banner_ad_2',
        'interstitial',
        'rewarded_video',
        'native',
        'code',
        'show_mrce_ads',
        'show_button_timer_ads',
        'show_banner_ads',
    ];
}
