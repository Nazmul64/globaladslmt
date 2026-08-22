<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerSetting extends Model
{
    use HasFactory;

    protected $table = 'server_settings';

    protected $fillable = [
        'server_mode',
        'local_url',
        'live_url',
    ];

    /**
     * Get default or first server setting.
     * If no record exists, create one with default values.
     */
    public static function getSetting()
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create([
                'server_mode' => 'local',
                'local_url' => 'http://10.0.2.2:8000',
                'live_url' => 'https://globalmoney.ltd',
            ]);
        }
        return $setting;
    }

    /**
     * Check if currently in local mode.
     */
    public function isLocal(): bool
    {
        return $this->server_mode === 'local';
    }

    /**
     * Get active server URL based on current server_mode.
     */
    public function getActiveUrlAttribute(): string
    {
        return $this->server_mode === 'live' ? $this->live_url : $this->local_url;
    }
}
