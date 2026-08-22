<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paymentmethod extends Model
{
    protected $fillable = [
        'method_name',
        'photo',
        'status',
        'method_number',
        'number_type',
        'usd_rate',
        'is_exchange_rate_active',
        'new_photo',
    ];
    protected $casts = [
        'method_number' => 'string',
        'is_exchange_rate_active' => 'boolean',
    ];

 public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function paymentMethodname()
{
    return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
}
}
