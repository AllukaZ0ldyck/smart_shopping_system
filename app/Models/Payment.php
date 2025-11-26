<?php

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',  
        'reference',
        'hitpay_payment_id',
        'amount',
        'currency',
        'email',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

}
