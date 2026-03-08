<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Product\Database\Seeders\ProductSeeder;
use App\Modules\Purchase\Database\Seeders\PurchaseSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            PurchaseSeeder::class,
        ]);
    }
}
