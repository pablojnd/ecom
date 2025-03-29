<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegúrate de tener usuarios y productos
        $users = User::all()->count() > 0 ? User::all() : User::factory(3)->create();
        $products = Product::all()->count() > 0 ? Product::all() : Product::factory(5)->create();

        foreach ($users as $user) {
            // Crear 1-3 órdenes para cada usuario
            Order::factory()
                ->count(rand(1, 3))
                ->withUser($user)
                ->create()
                ->each(function (Order $order) use ($products) {
                    // Añadir 1-5 productos a cada orden
                    $orderProducts = $products->random(rand(1, 5));
                    $total = 0;

                    foreach ($orderProducts as $product) {
                        $quantity = rand(1, 3);
                        $price = $product->price;
                        $subtotal = $quantity * $price;
                        $total += $subtotal;

                        // Crear detalle de la orden
                        $order->orderDetails()->create([
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price' => $price,
                        ]);
                    }

                    // Actualizar el total de la orden
                    $order->update(['total' => $total]);

                    // Crear pago para la orden
                    $order->payments()->create([
                        'amount' => $total,
                        'payment_status' => \App\Enums\PaymentStatusEnum::PENDING,
                    ]);
                });
        }
    }
}
