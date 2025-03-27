<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use BinaryCats\Sku\Concerns\SkuOptions;

class Product extends Model
{
    use HasFactory, HasUlids, HasSlug, HasSku;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'price',
        'offer_price',
        'description',
        'image_path',
        'stock_quantity',
        'sku',
        'is_active',
        'offer_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'offer_expires_at' => 'datetime',
        'price' => 'decimal:2',
        'offer_price' => 'decimal:2',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Opciones de configuración para la generación de SKU
     *
     * @return SkuOptions
     */
    public function skuOptions(): SkuOptions
    {
        return SkuOptions::make()
            ->from('name')
            ->target('sku')
            ->using('-')
            ->forceUnique(true)
            ->generateOnCreate(true)
            ->refreshOnUpdate(false);
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
