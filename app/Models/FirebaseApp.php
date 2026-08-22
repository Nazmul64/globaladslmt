<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirebaseApp extends Model
{
    use HasFactory;

    protected $table = 'firebase_apps';

    protected $fillable = [
        'app_name',
        'package_name',
        'firebase_credentials',
        'server_key',
        'is_active'
    ];

    protected $casts = [
        'firebase_credentials' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'firebase_app_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'firebase_app_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getProjectIdAttribute()
    {
        return $this->firebase_credentials['project_id'] ?? null;
    }

    // Static Methods
    public static function getByPackage($packageName)
    {
        return self::where('package_name', $packageName)->first();
    }
    public function firebaseApp()
{
    return $this->belongsTo(FirebaseApp::class, 'firebase_app_id');
}

public function scopeHasToken($query)
{
    return $query->whereNotNull('fcm_token');
}

public function scopeForApp($query, $firebaseAppId)
{
    return $query->where('firebase_app_id', $firebaseAppId);
}
}
