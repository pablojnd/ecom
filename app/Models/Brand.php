<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
