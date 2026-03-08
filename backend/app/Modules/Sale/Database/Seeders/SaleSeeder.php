<?php

namespace App\Modules\Sale\Database\Seeders;

use App\Modules\Sale\Models\Sale;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        // Venda 1: Cliente varejo com lucro positivo
        $sale1 = Sale::create([
            'customer' => 'Maria Silva Santos',
            'total_amount' => 0,
            'total_profit' => 0,
        ]);

        $items1 = [
            ['product_id' => 1, 'quantity' => 3, 'unit_sale_price' => 200.00],  // Lucro
            ['product_id' => 2, 'quantity' => 2, 'unit_sale_price' => 280.00],  // Lucro
        ];

        $total1 = 0;
        $profit1 = 0;

        foreach ($items1 as $item) {
            $product = Product::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                continue; // Pular se não houver estoque suficiente
            }

            $historicalCost = (float) $product->average_cost;
            $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

            $sale1->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_sale_price' => $item['unit_sale_price'],
                'historical_average_cost' => $historicalCost,
                'profit' => $profit,
            ]);

            $product->update([
                'stock' => $product->stock - $item['quantity'],
            ]);

            $total1 += $item['unit_sale_price'] * $item['quantity'];
            $profit1 += $profit;
        }

        $sale1->update([
            'total_amount' => $total1,
            'total_profit' => $profit1,
        ]);

        // Venda 2: Cliente corporativo com lucro
        $sale2 = Sale::create([
            'customer' => 'Empresa Tech Solutions Ltda',
            'total_amount' => 0,
            'total_profit' => 0,
        ]);

        $items2 = [
            ['product_id' => 5, 'quantity' => 4, 'unit_sale_price' => 550.00],  // Lucro
            ['product_id' => 6, 'quantity' => 5, 'unit_sale_price' => 350.00],  // Lucro
        ];

        $total2 = 0;
        $profit2 = 0;

        foreach ($items2 as $item) {
            $product = Product::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                continue;
            }

            $historicalCost = (float) $product->average_cost;
            $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

            $sale2->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_sale_price' => $item['unit_sale_price'],
                'historical_average_cost' => $historicalCost,
                'profit' => $profit,
            ]);

            $product->update([
                'stock' => $product->stock - $item['quantity'],
            ]);

            $total2 += $item['unit_sale_price'] * $item['quantity'];
            $profit2 += $profit;
        }

        $sale2->update([
            'total_amount' => $total2,
            'total_profit' => $profit2,
        ]);

        // Venda 3: Promoção com PREJUÍZO (preço abaixo do custo)
        $sale3 = Sale::create([
            'customer' => 'João Oliveira Costa',
            'total_amount' => 0,
            'total_profit' => 0,
        ]);

        $items3 = [
            ['product_id' => 3, 'quantity' => 2, 'unit_sale_price' => 350.00],  // PREJUÍZO (custo ~385)
            ['product_id' => 7, 'quantity' => 3, 'unit_sale_price' => 150.00],  // PREJUÍZO (custo ~175)
        ];

        $total3 = 0;
        $profit3 = 0;

        foreach ($items3 as $item) {
            $product = Product::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                continue;
            }

            $historicalCost = (float) $product->average_cost;
            $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

            $sale3->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_sale_price' => $item['unit_sale_price'],
                'historical_average_cost' => $historicalCost,
                'profit' => $profit,
            ]);

            $product->update([
                'stock' => $product->stock - $item['quantity'],
            ]);

            $total3 += $item['unit_sale_price'] * $item['quantity'];
            $profit3 += $profit;
        }

        $sale3->update([
            'total_amount' => $total3,
            'total_profit' => $profit3,
        ]);

        // Venda 4: Mix de lucro e prejuízo
        $sale4 = Sale::create([
            'customer' => 'Ana Paula Ferreira',
            'total_amount' => 0,
            'total_profit' => 0,
        ]);

        $items4 = [
            ['product_id' => 9, 'quantity' => 2, 'unit_sale_price' => 600.00],  // Lucro
            ['product_id' => 10, 'quantity' => 5, 'unit_sale_price' => 100.00], // PREJUÍZO (custo ~125)
        ];

        $total4 = 0;
        $profit4 = 0;

        foreach ($items4 as $item) {
            $product = Product::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                continue;
            }

            $historicalCost = (float) $product->average_cost;
            $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

            $sale4->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_sale_price' => $item['unit_sale_price'],
                'historical_average_cost' => $historicalCost,
                'profit' => $profit,
            ]);

            $product->update([
                'stock' => $product->stock - $item['quantity'],
            ]);

            $total4 += $item['unit_sale_price'] * $item['quantity'];
            $profit4 += $profit;
        }

        $sale4->update([
            'total_amount' => $total4,
            'total_profit' => $profit4,
        ]);

        // Venda 5: Cliente atacado com bom lucro
        $sale5 = Sale::create([
            'customer' => 'Distribuidora MegaStore Ltda',
            'total_amount' => 0,
            'total_profit' => 0,
        ]);

        $items5 = [
            ['product_id' => 4, 'quantity' => 6, 'unit_sale_price' => 130.00],  // Lucro
            ['product_id' => 8, 'quantity' => 3, 'unit_sale_price' => 400.00],  // Lucro
            ['product_id' => 1, 'quantity' => 2, 'unit_sale_price' => 190.00],  // Lucro
        ];

        $total5 = 0;
        $profit5 = 0;

        foreach ($items5 as $item) {
            $product = Product::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                continue;
            }

            $historicalCost = (float) $product->average_cost;
            $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

            $sale5->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_sale_price' => $item['unit_sale_price'],
                'historical_average_cost' => $historicalCost,
                'profit' => $profit,
            ]);

            $product->update([
                'stock' => $product->stock - $item['quantity'],
            ]);

            $total5 += $item['unit_sale_price'] * $item['quantity'];
            $profit5 += $profit;
        }

        $sale5->update([
            'total_amount' => $total5,
            'total_profit' => $profit5,
        ]);
    }
}
