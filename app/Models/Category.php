<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $fillable =[
     'category_name',
  ];
  public function posts()
{
    return $this->hasMany(Agentbuysellpost::class, 'category_id');
}
 public function agentBuySellPosts()
    {
        return $this->hasMany(Agentbuysellpost::class, 'category_id', 'id');
    }
}
