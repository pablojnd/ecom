<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear atributos comunes para productos
        $attributes = [
            'Color',
            'Tamaño',
            'Material',
            'Estilo',
        ];

        foreach ($attributes as $attributeName) {
            Attribute::create(['name' => $attributeName]);
        }
    }
}
