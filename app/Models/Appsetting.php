<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appsetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'appsettings';

    /**
     * The attributes that are mass assignable.
     */
protected $fillable = [
    'star_io_id','app_theme','home_icon_themes','currency_symbol','enabled',
    'task_rewards_level_1','task_rewards_level_2','task_rewards_level_3','task_rewards_level_4','task_rewards_level_5',
    'task_limit_level_1','task_limit_level_2','task_limit_level_3','task_limit_level_4','task_limit_level_5',
    'refer_commission','invalid_click_limit','invalid_deduct','view_before_click_view_target',
    'task_break_time_minutes','button_timer_seconds','statistics_point_rate','paywell_point_rate',
    'fixed_withdraw','vpn_modes','vpn_required_in_task_only','allowed_country','info_api_key',
    'telegram','whatsapp','email','how_to_work_link','privacy_policy',
    'registration_status','same_device_login','maintenance_mode','app_version','app_link'
];

}
