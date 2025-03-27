<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'status' => OrderStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Recalcula el total de la orden sumando todos sus detalles
     *
     * @return self
     */
    // public function recalculateTotal(): self
    // {
    //     $total = $this->orderDetails()->sum(DB::raw('price * quantity'));
    //     $this->update(['total' => $total]);

    //     return $this->fresh();
    // }

    /**
     * Actualiza el estado de pago basado en los pagos existentes
     *
     * @return self
     */
    public function updatePaymentStatus(): self
    {
        $paidAmount = $this->payments()
            ->where('status', \App\Enums\PaymentStatusEnum::PAID->value)
            ->sum('amount');

        if ($paidAmount >= $this->total) {
            $this->update(['payment_status' => \App\Enums\PaymentStatusEnum::PAID->value]);
        } elseif ($paidAmount > 0) {
            $this->update(['payment_status' => \App\Enums\PaymentStatusEnum::PENDING->value]);
        } else {
            $this->update(['payment_status' => \App\Enums\PaymentStatusEnum::PENDING->value]);
        }

        return $this->fresh();
    }
}
