<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Themechange extends Model
{
   // টেবিল নাম যদি themechanges না হয় তাহলে এটা লিখতে হবে
    protected $table = 'themechanges';

    // যেগুলো mass assignment করতে পারবা
    protected $fillable = [
        'color_code',
        'name',
        'is_active',
        'published_at',
    ];

    // যেগুলো auto cast হবে সঠিক টাইপে
    protected $casts = [
        'is_active'     => 'boolean',
        'published_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    // Scope: শুধু active theme গুলো নিতে চাইলে
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: latest published theme
    public function scopeLatestPublished($query)
    {
        return $query->whereNotNull('published_at')->orderBy('published_at', 'desc');
    }

    // Optional: যদি কখনো soft delete লাগে
    // use Illuminate\Database\Eloquent\SoftDeletes;
    // protected $dates = ['deleted_at'];
}
