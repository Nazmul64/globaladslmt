<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'post_comments';

    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
    ];

    // ✅ Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        // ✅ Use Creaetpost model and explicitly define foreign key
        return $this->belongsTo(Creaetpost::class, 'post_id', 'id');
    }
}
