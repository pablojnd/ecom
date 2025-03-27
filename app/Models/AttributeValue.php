<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'attribute_id',
        'value',
    ];

    /**
     * Obtener el atributo al que pertenece este valor
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Obtener los productos que tienen este valor de atributo
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_products')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }
}
