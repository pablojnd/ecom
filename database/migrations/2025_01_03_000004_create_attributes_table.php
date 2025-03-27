<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique(); // Nombre del atributo (ej: Color, Talla)
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('value'); // Valor del atributo (ej: Rojo, M, Algodón)
            $table->unique(['attribute_id', 'value']); // Asegura que no haya valores duplicados para el mismo atributo
            $table->timestamps();
        });

        Schema::create('attribute_products', function (Blueprint $table) {
            $table->foreignUlid('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignUlid('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->foreignUlid('attribute_value_id')->constrained('attribute_values')->onDelete('cascade'); // Cambio aquí: 'attribute_values' -> 'attribute_values'
            $table->primary(['product_id', 'attribute_id', 'attribute_value_id']); // Clave primaria compuesta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
