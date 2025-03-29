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
        'order_number',
        'total',
        'order_status',
        // 'payment_status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'order_status' => OrderStatusEnum::class,
        // 'payment_status' => PaymentStatusEnum::class,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Order $order) {
            // Generar número de orden numérico si no se ha proporcionado
            if (empty($order->order_number)) {
                // Obtener el último valor numérico y sumar 1r_number') + 1 ?? 10000;
                $lastOrder = self::max('order_number');
                $order->order_number = $lastOrder ? ($lastOrder + 1) : 10000; // Comenzar desde 10000
            }
        });
    }

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
    public function recalculateTotal(): self
    {
        $total = $this->orderDetails()->sum('subtotal');
        $this->update(['total' => $total]);

        return $this->fresh();
    }

    /**
     * Actualiza el estado de la orden basado en los pagos existentes
     *
     * @return self
     */
    public function updatePaymentStatus(): self
    {
        $paidAmount = $this->payments()
            ->where('payment_status', \App\Enums\PaymentStatusEnum::PAID->value)
            ->sum('amount');

        if ($paidAmount >= $this->total) {
            $this->update(['order_status' => \App\Enums\OrderStatusEnum::COMPLETED->value]);
        } elseif ($paidAmount > 0) {
            $this->update(['order_status' => \App\Enums\OrderStatusEnum::PROCESSING->value]);
        } else {
            $this->update(['order_status' => \App\Enums\OrderStatusEnum::PENDING->value]);
        }

        return $this->fresh();
    }

    /**
     * Obtiene el porcentaje de pago de la orden
     *
     * @return float
     */
    public function getPaymentPercentageAttribute(): float
    {
        $paidAmount = $this->payments()
            ->where('payment_status', \App\Enums\PaymentStatusEnum::PAID->value)
            ->sum('amount');

        return $this->total > 0 ? min(100, round(($paidAmount / $this->total) * 100)) : 0;
    }

    /**
     * Obtiene el estado de pago como texto descriptivo
     *
     * @return string
     */
    public function getPaymentStatusTextAttribute(): string
    {
        $percentage = $this->payment_percentage;

        if ($percentage >= 100) {
            return 'Pagado (100%)';
        } elseif ($percentage > 0) {
            return "Pago parcial ({$percentage}%)";
        } else {
            return 'Pendiente de pago';
        }
    }

    /**
     * Obtiene el color del estado de pago
     *
     * @return string
     */
    public function getPaymentStatusColorAttribute(): string
    {
        $percentage = $this->payment_percentage;

        if ($percentage >= 100) return 'success';
        if ($percentage > 0) return 'warning';
        return 'danger';
    }

    // Agregar este método si deseas un formato de presentación con prefijo
    public function getFormattedOrderNumberAttribute(): string
    {
        return 'ORD-' . $this->order_number;
    }
}
