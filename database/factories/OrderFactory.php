<?php

namespace Database\Factories;

use App\Models\User;
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
            'user_id' => User::factory(),
            'total' => $this->faker->randomFloat(2, 100, 1000),
            'status' => \App\Enums\OrderStatusEnum::PENDING,
            'payment_status' => \App\Enums\PaymentStatusEnum::PENDING,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function withUser(User $user = null)
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
