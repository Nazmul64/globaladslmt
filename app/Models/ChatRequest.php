<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRequest extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
        'message',
        'image',
        'is_read'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }


     protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sender (user who sent the request).
     */


    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for accepted requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is accepted.
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }



}
