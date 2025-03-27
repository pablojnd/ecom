<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Attributevalue;
use Illuminate\Database\Seeder;

class AttributeValueseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapa de valores predefinidos para cada atributo
        $attributeValues = [
            'Color' => ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco', 'Gris'],
            'Tamaño' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'Material' => ['Algodón', 'Poliéster', 'Cuero', 'Madera', 'Metal', 'Plástico'],
            'Estilo' => ['Casual', 'Formal', 'Deportivo', 'Elegante', 'Vintage', 'Moderno'],
        ];

        // Obtener atributos existentes o crearlos si no existen
        $attributes = Attribute::all();

        if ($attributes->isEmpty()) {
            $this->call(AttributeSeeder::class);
            $attributes = Attribute::all();
        }

        // Crear valores para cada atributo
        foreach ($attributes as $attribute) {
            if (isset($attributeValues[$attribute->name])) {
                foreach ($attributeValues[$attribute->name] as $value) {
                    Attributevalue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ]);
                }
            } else {
                // Si no hay valores predefinidos, crear algunos aleatorios
                Attributevalue::factory()
                    ->count(5)
                    ->forAttribute($attribute)
                    ->create();
            }
        }
    }
}
