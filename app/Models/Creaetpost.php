<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Creaetpost extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Table name
     */
    protected $table = 'creaetposts';

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'user_id',
        'content',
        'image',
        'privacy',
        'is_active',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_active' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Default values
     */
    protected $attributes = [
        'is_active' => true,
        'likes_count' => 0,
        'comments_count' => 0,
        'shares_count' => 0,
        'privacy' => 'public',
    ];

    /**
     * Relationship: Post belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Post has many Likes
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    /**
     * Relationship: Post has many Comments
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Post has many Shares
     */
    public function shares()
    {
        return $this->hasMany(PostShare::class, 'post_id');
    }

    /**
     * Check if current user has liked this post
     */
    public function isLikedByUser($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Scope: Active posts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Public posts
     */
    public function scopePublic($query)
    {
        return $query->where('privacy', 'public');
    }

    /**
     * Scope: Posts by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }

    /**
     * Get formatted created_at
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y');
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
