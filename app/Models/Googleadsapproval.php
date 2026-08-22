<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Googleadsapproval extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'googleadsapprovals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'approval_text',
    ];
}
