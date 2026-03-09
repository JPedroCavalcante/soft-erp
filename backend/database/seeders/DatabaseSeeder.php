<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Product\Database\Seeders\ProductSeeder;
use App\Modules\Purchase\Database\Seeders\PurchaseSeeder;
use App\Modules\Sale\Database\Seeders\SaleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            PurchaseSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
