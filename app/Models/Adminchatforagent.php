<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adminchatforagent extends Model
{
     protected $fillable = [
        'sender_id', 'receiver_id', 'message', 'image', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }
}
