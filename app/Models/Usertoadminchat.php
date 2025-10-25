<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usertoadminchat extends Model
{
    protected $fillable =[
        'sender_id',
        'receiver_id',
        'message',
        'image',
        'is_read',
        'new_image',

    ];
     public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
