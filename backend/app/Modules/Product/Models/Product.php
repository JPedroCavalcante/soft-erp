<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sale_price',
        'stock',
        'average_cost',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected static function newFactory()
    {
        return \App\Modules\Product\Database\Factories\ProductFactory::new();
    }
}
