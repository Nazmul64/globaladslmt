<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appsetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appsettings';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'star_io_id',
    //     'enabled',

    //     // Invalid Click Protection
    //     'invalid_click_limit',
    //     'invalid_deduct',

    //     // Task Timer Settings
    //     'task_break_time_minutes',
    //     'button_timer_seconds',
    //     'view_before_click_view_target',
    //     'ad_timer_seconds',

    //     // VPN & Location Control
    //     'vpn_modes',
    //     'vpn_required_in_task_only',
    //     'allowed_country',

    //     // API & App Control
    //     'info_api_key',
    //     'registration_status',
    //     'same_device_login',
    //     'maintenance_mode',
    //     'app_version',
    //     'app_link',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
        'registration_status' => 'string',
        'same_device_login' => 'string',
        'maintenance_mode' => 'string',
        'vpn_required_in_task_only' => 'string',
        'stario_timer_status' => 'string',
        'admob_timer_status' => 'string',

        // 🔥 Task Timer Settings - integer cast
        'task_break_time_minutes' => 'integer',
        'button_timer_seconds' => 'integer',
        'ad_timer_seconds' => 'integer',
        'view_before_click_view_target' => 'integer',

        // Invalid Click Protection
        'invalid_click_limit' => 'integer',
        'invalid_deduct' => 'decimal:2',
    ];
     protected $fillable = [
        // Google AdMob Settings
        'admob_app_id',
        'admob_banner_id',
        'admob_interstitial_id',
        'admob_rewarded_interstitial_id',
        'admob_rewarded_id',
        'admob_native_id',
        'admob_app_open_id',
        'admob_status',
        'admob_timer_status',

        // Basic App Settings
        'star_io_id',
        'stario_timer_status',
        'invalid_click_limit',
        'invalid_deduct',
        'view_before_click_view_target',

        // Timer Settings
        'task_break_time_minutes',
        'button_timer_seconds',
        'ad_timer_seconds',

        // VPN & Country Settings
        'vpn_modes',
        'vpn_required_in_task_only',
        'allowed_country',

        // App Control Settings
        'registration_status',
        'same_device_login',
        'maintenance_mode',
        'app_version',
        'app_link',
    ];

    /**
     * 🎯 HELPER: Get break time in seconds for native timer
     */
    public function getBreakTimeInSeconds(): int
    {
        return (int) ($this->task_break_time_minutes ?? 1) * 60;
    }

    /**
     * 🎯 HELPER: Get ad timer in seconds
     */
    public function getAdTimerSeconds(): int
    {
        return (int) ($this->ad_timer_seconds ?? 30);
    }

     public static function getDefault()
    {
        return self::first();
    }

    /**
     * Check if VPN is required
     */
    public function isVpnRequired(): bool
    {
        return in_array(strtolower((string)$this->vpn_modes), ['required', 'yes', 'allowed']);
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(): bool
    {
        return strtolower((string)$this->registration_status) !== 'closed';
    }

    /**
     * Check if app is in maintenance mode
     */
    public function isInMaintenance(): bool
    {
        return in_array(strtolower((string)$this->maintenance_mode), ['yes', '1', 'true']);
    }

    /**
     * Check if same device login is allowed
     */
    public function allowsSameDeviceLogin(): bool
    {
        return in_array(strtolower((string)$this->same_device_login), ['yes', '1', 'true']);
    }

    /**
     * Get allowed countries as array
     */
    public function getAllowedCountriesArray(): array
    {
        if (empty($this->allowed_country)) {
            return [];
        }

        return array_map('trim', explode(',', $this->allowed_country));
    }
}
