<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'product_name',
        'slug',
        'images',
        'description',
        'price',
        'is_active',
        'is_featured',
        'is_stock',
        'on_sale',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'integer',
    ];

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.'); // Formato con punto para miles y coma para decimales
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
