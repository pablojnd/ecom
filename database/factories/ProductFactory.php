<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => \App\Models\Brand::inRandomOrder()->first(),
            'category_id' => \App\Models\Category::inRandomOrder()->first(),
            'name' => fake()->name,
            // 'slug' => fake()->slug,
            'price' => fake()->randomFloat(2, 1, 1000),
            'offer_price' => fake()->optional(0.3)->randomFloat(2, 1, 800),
            'image_path' => fake()->imageUrl(),
            'description' => fake()->paragraph(),
            'is_active' => fake()->boolean(80),
            // 'sku' => fake()->unique()->ean13(),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'offer_expires_at' => fake()->optional(0.3)->dateTimeBetween('+1 week', '+6 months'),
        ];
    }
}
