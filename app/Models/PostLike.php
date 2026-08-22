<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory;

    protected $table = 'post_likes';

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    // // ✅ Relations
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    // public function post()
    // {
    //     // ✅ Use Creaetpost model and explicitly define foreign key
    //     return $this->belongsTo(Creaetpost::class, 'post_id', 'id');
    // }

    public function post()
{
    return $this->belongsTo(Creaetpost::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

}
