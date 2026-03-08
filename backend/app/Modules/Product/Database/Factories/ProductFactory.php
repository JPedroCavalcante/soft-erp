<?php

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $productTypes = [
            'Notebook',
            'Mouse',
            'Teclado',
            'Monitor',
            'Impressora',
            'Cadeira',
            'Mesa',
            'Webcam',
            'Headset',
            'HD Externo',
            'SSD',
            'Memória RAM',
            'Processador',
            'Placa de Vídeo',
            'Fonte',
        ];

        $brands = [
            'Dell',
            'HP',
            'Lenovo',
            'Samsung',
            'LG',
            'Logitech',
            'Razer',
            'Corsair',
            'Kingston',
            'Western Digital',
        ];

        $productType = fake()->randomElement($productTypes);
        $brand = fake()->randomElement($brands);

        return [
            'name' => "{$productType} {$brand} " . fake()->word(),
            'sale_price' => fake()->randomFloat(2, 10.00, 500.00),
            'stock' => 0,
            'average_cost' => 0,
        ];
    }
}
