<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
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
            'street_address' => fake()->streetAddress(), // Dirección
            'country' => fake()->country(), // País
            'state' => fake()->state(), // Estado
            'city' => fake()->city(), // Ciudad
        ];
    }
}
