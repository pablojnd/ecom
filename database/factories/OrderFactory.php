<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\PayMethod;
use App\Enums\PayStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Relación con usuario
            'pay_method' => fake()->randomElement(PayMethod::cases())->value, // Método de pago
            'pay_status' => fake()->randomElement(PayStatus::cases())->value, // Estado de pago
            'order_status' => fake()->randomElement(OrderStatus::cases())->value, // Estado de la orden
            'grand_total' => fake()->numberBetween(1000, 50000), // Total de la orden
        ];
    }
}
