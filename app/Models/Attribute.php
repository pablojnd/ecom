<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
    ];

    /**
     * Obtener los valores asociados a este atributo
     */
    public function values(): HasMany
    {
        return $this->hasMany(Attributevalue::class);
    }

    /**
     * Obtener los productos asociados a este atributo
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_products')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }
}
