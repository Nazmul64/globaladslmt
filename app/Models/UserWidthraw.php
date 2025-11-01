<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWidthraw extends Model
{
    protected $fillable = [
        'payment_method_id',
        'account_number',
        'wallet_address',
        'user_id',
        'amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function paymentMethod()
    {
        return $this->belongsTo(Paymentmethod::class, 'payment_method_id');
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
  public function payment_name()
    {
        return $this->belongsTo(Paymentmethod::class, 'payment_method_id');
    }

}
