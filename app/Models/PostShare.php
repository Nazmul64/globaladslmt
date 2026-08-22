<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostShare extends Model
{
    use HasFactory;

    protected $table = 'post_shares';

    protected $fillable = [
        'user_id',
        'post_id',
        'share_content',
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
