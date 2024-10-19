<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        // 'username',
        // 'client_fullname',
        // 'client_email',
        // 'client_dob',
        'meja',
        'order_items',
        'quantity',
        'price_total',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'order_items' => 'array',
    ];

    protected static function booted()
    {
        static::created(function ($order) {
            Log::info('Order created: ' . $order->id);
        });
    }
    public function setOrderItemsAttribute($value)
    {
        $this->attributes['order_items'] = json_encode($value);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
