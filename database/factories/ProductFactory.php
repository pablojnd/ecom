<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
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
            'product_name' => fake()->words(3, true), // Nombre del producto
            'slug' => fake()->unique()->slug(), // Slug único
            'images' => [ // Array de URLs de imágenes ficticias
                fake()->imageUrl(640, 480, 'product', true, 'Faker'),
                fake()->imageUrl(640, 480, 'product', true, 'Faker'),
                fake()->imageUrl(640, 480, 'product', true, 'Faker'),
            ],
            'description' => fake()->paragraph(), // Descripción detallada
            'price' => fake()->numberBetween(1000, 50000), // Precio
            // 'stock' => fake()->numberBetween(0, 100), // Stock
            'brand_id' => Brand::factory(), // Relación con marca
            'category_id' => Category::factory(), // Relación con categoría
        ];
    }
}
