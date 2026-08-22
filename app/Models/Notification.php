<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'firebase_app_id',
        'title',
        'message',
        'image_url',
        'action_url',
        'notification_type',
        'send_to',
        'user_ids',
        'total_sent',
        'total_failed',
        'recipients_count',
        'is_read',
        'sent_at'
    ];

    protected $casts = [
        'firebase_app_id' => 'integer',
        'user_ids' => 'array',
        'total_sent' => 'integer',
        'total_failed' => 'integer',
        'recipients_count' => 'integer',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->sent_at) {
                $model->sent_at = now();
            }
        });
    }

    // Relationships
    public function firebaseApp()
    {
        return $this->belongsTo(FirebaseApp::class, 'firebase_app_id');
    }

    // Accessors
    public function getSuccessRateAttribute()
    {
        $total = $this->total_sent + $this->total_failed;
        if ($total == 0) {
            return 0;
        }
        return round(($this->total_sent / $total) * 100, 2);
    }

    public function getTotalUsersAttribute()
    {
        if ($this->send_to == 'specific' && $this->user_ids) {
            return count($this->user_ids);
        }
        return 0;
    }
}
