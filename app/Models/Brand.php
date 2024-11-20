<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'slug',
        'image',
        'is_active',
        'parent_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
