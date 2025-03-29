<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener órdenes o crear si no existen
        $orders = Order::all()->count() > 0 ? Order::all() : Order::factory(5)->create();

        // Para cada orden, crear un pago
        foreach ($orders as $order) {
            Payment::factory()
                ->forOrder($order)
                ->create([
                    'amount' => $order->total,
                    'payment_status' => \App\Enums\PaymentStatusEnum::PAID->value,
                ]);

            // Actualizar el estado de la orden
            $order->update([
                'order_status' => \App\Enums\OrderStatusEnum::COMPLETED->value,
            ]);
        }
    }
}
