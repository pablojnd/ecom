<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Attributevalue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attributevalue>
 */
class AttributeValueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attributevalue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Mapa de posibles valores para tipos comunes de atributos
        $attributeValues = [
            'Color' => ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco', 'Gris', 'Amarillo', 'Naranja', 'Morado', 'Rosa'],
            'Tamaño' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'Material' => ['Algodón', 'Poliéster', 'Cuero', 'Madera', 'Metal', 'Plástico', 'Vidrio', 'Cerámica'],
            'Estilo' => ['Casual', 'Formal', 'Deportivo', 'Elegante', 'Vintage', 'Moderno', 'Clásico'],
            'default' => [fake()->word(), fake()->colorName(), fake()->numberBetween(1, 100) . ' cm']
        ];

        return [
            'attribute_id' => Attribute::factory(),
            'value' => function (array $attributes) use ($attributeValues) {
                $attribute = Attribute::find($attributes['attribute_id']);

                if (!$attribute) {
                    return fake()->word();
                }

                $attributeName = $attribute->name;

                if (isset($attributeValues[$attributeName])) {
                    return fake()->unique()->randomElement($attributeValues[$attributeName]);
                }

                return fake()->unique()->randomElement($attributeValues['default']);
            },
        ];
    }

    /**
     * Indica que el valor pertenece a un atributo específico
     */
    public function forAttribute(Attribute $attribute)
    {
        return $this->state(function () use ($attribute) {
            return [
                'attribute_id' => $attribute->id,
            ];
        });
    }
}
