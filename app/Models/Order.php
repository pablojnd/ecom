<?php

namespace App\Models;

use App\Enums\PayMethod;
use App\Enums\PayStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pay_status',
        'pay_method',
        'order_status',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'pay_method' => PayMethod::class,
        'pay_status' => PayStatus::class,
        'order_status' => OrderStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
