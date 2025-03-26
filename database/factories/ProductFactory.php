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
            'slug' => fake()->slug,
            'price' => fake()->randomFloat(2, 1, 1000),
            'image_path' => fake()->imageUrl(),
            'description' => fake()->sentence,
        ];
    }
}
