<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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

];
protected $casts = [
    'refer_income' => 'float',
    'generation_income' => 'float',
];

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
    public function deposits()
    {
        return $this->hasMany(Deposite::class);
    }

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
}
