<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
    ];

    // No necesita HasUlids porque usa una clave primaria compuesta, no un ID único

    /**
     * Indicar que la tabla no tiene una clave primaria autoincremental
     */
    public $incrementing = false;

    /**
     * Obtener el producto asociado a esta relación
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtener el atributo asociado a esta relación
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Obtener el valor de atributo asociado a esta relación
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(Attributevalue::class, 'attribute_value_id');
    }
}
