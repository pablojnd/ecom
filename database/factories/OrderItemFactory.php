<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(), // Relación con orden
            'product_id' => Product::factory(), // Relación con producto
            'quantity' => fake()->numberBetween(1, 20), // Cantidad
            'unit_amount' => fake()->numberBetween(1000, 50000), // Precio
            'total_amount' => fake()->numberBetween(1000, 50000), // Precio
        ];
    }
}
