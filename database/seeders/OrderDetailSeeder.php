<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener órdenes y productos o crear si no existen
        $orders = Order::all()->count() > 0 ? Order::all() : Order::factory(3)->create();
        $products = Product::all()->count() > 0 ? Product::all() : Product::factory(5)->create();

        // Para cada orden, crear detalles con productos aleatorios
        foreach ($orders as $order) {
            $orderTotal = 0;

            // Añadir 2-4 productos a cada orden
            $orderProducts = $products->random(rand(2, 4));

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price ?? rand(10, 100);
                $subtotal = $quantity * $price;
                $orderTotal += $subtotal;

                OrderDetail::factory()
                    ->forOrder($order)
                    ->forProduct($product)
                    ->create([
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
            }

            // Actualizar el total de la orden
            $order->update(['total' => $orderTotal]);
        }
    }
}
