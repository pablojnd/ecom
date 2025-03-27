<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeProduct;
use App\Models\Attributevalue;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeProduct>
 */
class AttributeProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttributeProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = Attributevalue::factory()->forAttribute($attribute)->create();

        return [
            'product_id' => Product::factory(),
            'attribute_id' => $attribute->id,
            'attribute_value_id' => $attributeValue->id,
        ];
    }

    /**
     * Indica que la relación es para un producto específico
     */
    public function forProduct(Product $product)
    {
        return $this->state(function () use ($product) {
            return [
                'product_id' => $product->id,
            ];
        });
    }

    /**
     * Indica que la relación es para un atributo y valor específicos
     */
    public function forAttributeValue(Attributevalue $attributeValue)
    {
        return $this->state(function () use ($attributeValue) {
            return [
                'attribute_id' => $attributeValue->attribute_id,
                'attribute_value_id' => $attributeValue->id,
            ];
        });
    }
}
