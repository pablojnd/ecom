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
        Schema::dropIfExists('attribute_products');
    }
};
