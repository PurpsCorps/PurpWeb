<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'username',
        'client_fullname',
        'client_email',
        'client_dob',
        'product',
        'product_label',
        'product_price',
        'quantity',
        'price_total',
        'payment_method',
        'status',
    ];
}
