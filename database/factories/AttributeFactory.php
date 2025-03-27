<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Nombres comunes de atributos para productos
        $attributeNames = [
            'Color',
            'Tamaño',
            'Material',
            'Estilo',
            'Peso',
            'Altura',
            'Anchura',
            'Profundidad',
            'Capacidad',
            'Potencia',
        ];

        return [
            'name' => fake()->unique()->randomElement($attributeNames) ?: fake()->unique()->word(),
        ];
    }
}
