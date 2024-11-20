<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456')
        ]);

        // Crear marcas y categorías
        // Brand::factory(10)->create();
        // Category::factory(5)->create();

        // Crear usuarios con direcciones
        User::factory(10)->create()->each(function ($user) {
            Address::factory(2)->create(['user_id' => $user->id]); // Crear 2 direcciones por usuario
        });

        // Crear productos
        Product::factory(10)->create();

        // Crear órdenes y items de orden
        Order::factory(30)->create()->each(function ($order) {
            // Agregar items a cada orden
            OrderItem::factory(rand(1, 5))->create(['order_id' => $order->id]);
        });
    }
}
