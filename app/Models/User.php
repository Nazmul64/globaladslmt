<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
     use HasApiTokens, Notifiable;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
   protected $fillable = [
    'balance',
    'confirm_password',
    'country',
    'email',
    'generation_income',
    'mobile',
    'name',
    'password',
    'ref_code',
    'ref_id',
    'referred_by',
    'refer_income',
    'role',
    'status',
    'wallet_address',
    'photo',
    'is_blocked',
    'new_photo',
    'package_id',
    'firebase_app_id',
    'firebase_app_id',
    'fcm_token',
    'device_type',
    'device_id',
    'last_notification_at',
    'locked_amount',  // ✅ Added
    'is_locked_override',
     // ✅ Add OneSignal fields to fillable
        'onesignal_player_id',
        'onesignal_updated_at',


];
protected $casts = [
    'refer_income' => 'float',
    'generation_income' => 'float',
    'email_verified_at' => 'datetime',
    'last_active_at' => 'datetime',
    'onesignal_updated_at' => 'datetime',
    'locked_amount' => 'decimal:2',  // ✅ Added
    'is_locked_override' => 'boolean', // ✅ NEW
];

protected $appends = [
    'is_verified',
    'photo_url',
];

public function getIsVerifiedAttribute(): bool
{
    return \App\Models\Kyc::where('user_id', $this->id)->where('status', 'approved')->exists();
}

public function getPhotoUrlAttribute(): ?string
{
    $photo = $this->photo ?? $this->new_photo ?? $this->profile_photo ?? null;
    if (!$photo) {
        return null;
    }
    if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
        return $photo;
    }
    if (file_exists(public_path('uploads/profile/' . $photo))) {
        return asset('uploads/profile/' . $photo);
    }
    return asset('storage/' . $photo);
}

  public function agentDeposites()
    {
        return $this->hasMany(AgentDeposite::class, 'agent_id');
    }

    /**
     * ✅ Get total approved deposits for this agent
     */
    public function getTotalApprovedDepositAttribute()
    {
        return $this->agentDeposites()
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * ✅ Get available balance (Total Deposit - Locked Amount)
     */
    public function getAvailableBalanceAttribute()
    {
        $totalDeposit = $this->total_approved_deposit;
        return max(0, $totalDeposit - $this->locked_amount);
    }

public function getBalanceAttribute($value)
{
    return $value;
}

public function setBalanceAttribute($value)
{
    $this->attributes['balance'] = $value;
}
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }




    // Relation to user's deposits
    // public function deposits()
    // {
    //     return $this->hasMany(Deposite::class);
    // }

public function packagebuys()
{
    return $this->hasMany(Packagebuy::class, 'user_id');
}

public function referrals()
{
    return $this->hasMany(User::class, 'referred_by');
}
public function kyc()
{
    return $this->hasOne(Kyc::class);
}
public function agentkyc()
{
    return $this->hasOne(Agentkyc::class, 'user_id');
}

    public function sentMessages()
    {
        return $this->hasMany(Usertoagentchat::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Usertoagentchat::class, 'receiver_id');
    }

    /**
     * Get all messages (sent and received).
     */
    public function allMessages()
    {
        return Usertoagentchat::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get unread message count.
     */
    public function unreadMessageCount(): int
    {
        return $this->receivedMessages()
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get conversation with specific user.
     */
    public function conversationWith($userId)
    {
        return Usertoagentchat::conversation($this->id, $userId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
public function receivedChatRequests()
{
    return $this->hasMany(ChatRequest::class, 'receiver_id');
}
public function deposits()
    {
        return $this->hasMany(Userdepositerequest::class);
    }

    // Relation with withdraws
    public function withdraws()
    {
        return $this->hasMany(UserWidhrawrequest::class);
    }


public function sentChatRequests()
{
    return $this->hasMany(ChatRequest::class, 'sender_id');
}

  public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    /**
     * Get the package that the user belongs to
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get today's earning record
     */
    public function todayEarning()
    {
        return $this->hasOne(Earning::class)
            ->whereDate('earning_date', today());
    }
    public function posts()
{
    return $this->hasMany(Creaetpost::class);
}

/**
 * Get all likes by the user.
 */
public function likes()
{
    return $this->hasMany(PostLike::class);
}

/**
 * Get all comments by the user.
 */
public function comments()
{
    return $this->hasMany(PostComment::class);
}

/**
 * Get all shares by the user.
 */
public function shares()
{
    return $this->hasMany(PostShare::class);
}
public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token, $this->email));
}

public function agentPosts()
{
    return $this->hasMany(Agentbuysellpost::class, 'agent_id');
}

public function depositRequests()
{
    return $this->hasMany(Userdepositerequest::class, 'user_id');
}

public function withdrawRequests()
{
    return $this->hasMany(UserWidhrawrequest::class, 'user_id');
}

public function kycagent(){
    return $this->belongsTo(User::class, 'user_id');
}
public function withdrawals()
{
    return $this->hasMany(\App\Models\UserWidthraw::class);
}
public function deposites()
{
    return $this->hasMany(\App\Models\Deposite::class);
}
public function userWidthraws()
{
    return $this->hasMany(UserWidthraw::class);
}

public function paymentMethods()
    {
        return $this->hasMany(Agentpaymentmethod::class, 'agent_id', 'id')
                    ->where('status', 'active')
                    ->select('id', 'agent_id', 'method_name', 'method_number')
                    ->orderBy('method_name', 'asc');
    }

    // Relationships
    public function firebaseApp()
    {
        return $this->belongsTo(FirebaseApp::class);
    }



    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Scopes
    public function scopeWithFcmToken($query)
    {
        return $query->whereNotNull('fcm_token');
    }

    public function scopeActiveUsers($query)
    {
        return $query->where('is_blocked', false)
                     ->whereIn('status', ['approved', 'pending']);
    }

    public function scopeByFirebaseApp($query, $appId)
    {
        return $query->where('firebase_app_id', $appId);
    }

    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    // Helper Methods
    public function hasFcmToken(): bool
    {
        return !empty($this->fcm_token);
    }

    public function updateFcmToken(string $token, ?string $deviceType = null): void
    {
        $this->update([
            'fcm_token' => $token,
            'device_type' => $deviceType ?? $this->device_type,
            'fcm_updated_at' => now(),
        ]);
    }

    public function clearFcmToken(): void
    {
        $this->update([
            'fcm_token' => null,
            'fcm_updated_at' => null,
        ]);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id')->latest();
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id')->latest();
    }

    public function getFriendsAttribute()
    {
        $userId = $this->id;
        $sentAccepted = ChatRequest::where('sender_id', $userId)->where('status', 'accepted')->pluck('receiver_id');
        $receivedAccepted = ChatRequest::where('receiver_id', $userId)->where('status', 'accepted')->pluck('sender_id');
        $friendIds = $sentAccepted->merge($receivedAccepted)->unique();
        return User::whereIn('id', $friendIds)->get();
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getDisplayEmailAttribute(): string
    {
        return $this->email ?? 'No Email';
    }

}


