<?php

use App\Enums\PayMethod;
use App\Enums\PayStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('pay_method', array_column(PayMethod::cases(), 'value'))->default(PayMethod::Cash->value);
            $table->enum('pay_status', array_column(PayStatus::cases(), 'value'))->default(PayStatus::Pending->value);
            $table->enum('order_status', array_column(OrderStatus::cases(), 'value'))->default(OrderStatus::New->value);
            // $table->decimal('shipping_amount', 10, 2)->nullable();
            $table->integer('grand_total')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
