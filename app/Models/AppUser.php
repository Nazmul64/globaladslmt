<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    use HasFactory;

    protected $table = 'app_users';

    protected $fillable = [
        'firebase_app_id',
        'user_name',
        'user_email',
        'password',
        'fcm_token',
        'device_type'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'firebase_app_id' => 'integer',
    ];

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

    public static function getByEmail($email)
    {
        return self::where('user_email', $email)->first();
    }

    public static function getByAppWithToken($firebaseAppId)
    {
        return self::where('firebase_app_id', $firebaseAppId)
            ->whereNotNull('fcm_token')
            ->get();
    }
}
