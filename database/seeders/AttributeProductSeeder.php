<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Attributevalue;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AttributeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener productos, atributos y valores existentes o crearlos si no existen
        $products = Product::all();

        if ($products->isEmpty()) {
            $products = Product::factory()->count(10)->create();
        }

        // Asegurarse de que hay atributos y valores disponibles
        $attributes = Attribute::all();

        if ($attributes->isEmpty()) {
            $this->call([
                AttributeSeeder::class,
                Attributevalueseeder::class,
            ]);

            $attributes = Attribute::all();
        }

        // Para cada producto, asignar algunos atributos con valores aleatorios
        foreach ($products as $product) {
            // Asignar entre 1 y 3 atributos aleatorios a cada producto
            $randomAttributes = $attributes->random(rand(1, min(3, $attributes->count())));

            foreach ($randomAttributes as $attribute) {
                // Obtener valores disponibles para este atributo
                $values = Attributevalue::where('attribute_id', $attribute->id)->get();

                if ($values->isNotEmpty()) {
                    // Seleccionar un valor aleatorio
                    $randomValue = $values->random();

                    // Crear la relación producto-atributo-valor
                    $product->attributes()->attach($attribute->id, [
                        'attribute_value_id' => $randomValue->id,
                    ]);
                }
            }
        }
    }
}
