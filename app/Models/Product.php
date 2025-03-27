<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, HasUlids, HasSlug;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'price',
        'description',
        'image_path',
        'stock_quantity',
        'sku',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Obtener los atributos asociados a este producto
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_products')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    /**
     * Obtener los valores de atributos asociados a este producto
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(Attributevalue::class, 'attribute_products', 'product_id', 'attribute_value_id')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }
}
