<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoHealthLog extends Model
{
    protected $fillable = [
        'url',
        'referer',
        'user_agent',
        'ip_address',
        'hits',
    ];
}
