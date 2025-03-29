<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'payment_status' => fake()->randomElement(\App\Enums\PaymentStatusEnum::cases()),
            'payment_method' => fake()->randomElement(\App\Enums\PaymentMethodEnum::cases()),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function forOrder(Order $order)
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);
    }
}
