<?php

namespace App\Modules\Purchase\Database\Seeders;

use App\Modules\Purchase\Models\Purchase;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $purchase1 = Purchase::create([
            'supplier' => 'Fornecedor Tech Solutions Ltda',
            'total_amount' => 0,
        ]);

        $items1 = [
            ['product_id' => 1, 'quantity' => 10, 'unit_price' => 150.50],
            ['product_id' => 2, 'quantity' => 15, 'unit_price' => 220.00],
            ['product_id' => 3, 'quantity' => 8, 'unit_price' => 380.75],
        ];

        $total1 = 0;
        foreach ($items1 as $item) {
            $purchase1->items()->create($item);

            $product = Product::find($item['product_id']);
            $currentStock = $product->stock;
            $currentCost = (float) $product->average_cost;
            $newQuantity = $item['quantity'];
            $newPrice = (float) $item['unit_price'];

            if ($currentStock + $newQuantity > 0) {
                $newAverageCost = (($currentCost * $currentStock) + ($newPrice * $newQuantity))
                    / ($currentStock + $newQuantity);
            } else {
                $newAverageCost = $newPrice;
            }

            $product->update([
                'stock' => $currentStock + $newQuantity,
                'average_cost' => $newAverageCost,
            ]);

            $total1 += $newPrice * $newQuantity;
        }

        $purchase1->update(['total_amount' => $total1]);

        $purchase2 = Purchase::create([
            'supplier' => 'Distribuidora InfoMax',
            'total_amount' => 0,
        ]);

        $items2 = [
            ['product_id' => 4, 'quantity' => 20, 'unit_price' => 95.00],
            ['product_id' => 5, 'quantity' => 12, 'unit_price' => 450.00],
        ];

        $total2 = 0;
        foreach ($items2 as $item) {
            $purchase2->items()->create($item);

            $product = Product::find($item['product_id']);
            $currentStock = $product->stock;
            $currentCost = (float) $product->average_cost;
            $newQuantity = $item['quantity'];
            $newPrice = (float) $item['unit_price'];

            if ($currentStock + $newQuantity > 0) {
                $newAverageCost = (($currentCost * $currentStock) + ($newPrice * $newQuantity))
                    / ($currentStock + $newQuantity);
            } else {
                $newAverageCost = $newPrice;
            }

            $product->update([
                'stock' => $currentStock + $newQuantity,
                'average_cost' => $newAverageCost,
            ]);

            $total2 += $newPrice * $newQuantity;
        }

        $purchase2->update(['total_amount' => $total2]);

        $purchase3 = Purchase::create([
            'supplier' => 'Mega Importadora Brasil',
            'total_amount' => 0,
        ]);

        $items3 = [
            ['product_id' => 6, 'quantity' => 18, 'unit_price' => 280.50],
            ['product_id' => 7, 'quantity' => 10, 'unit_price' => 175.00],
            ['product_id' => 8, 'quantity' => 14, 'unit_price' => 320.90],
        ];

        $total3 = 0;
        foreach ($items3 as $item) {
            $purchase3->items()->create($item);

            $product = Product::find($item['product_id']);
            $currentStock = $product->stock;
            $currentCost = (float) $product->average_cost;
            $newQuantity = $item['quantity'];
            $newPrice = (float) $item['unit_price'];

            if ($currentStock + $newQuantity > 0) {
                $newAverageCost = (($currentCost * $currentStock) + ($newPrice * $newQuantity))
                    / ($currentStock + $newQuantity);
            } else {
                $newAverageCost = $newPrice;
            }

            $product->update([
                'stock' => $currentStock + $newQuantity,
                'average_cost' => $newAverageCost,
            ]);

            $total3 += $newPrice * $newQuantity;
        }

        $purchase3->update(['total_amount' => $total3]);

        $purchase4 = Purchase::create([
            'supplier' => 'Comercial Digital World',
            'total_amount' => 0,
        ]);

        $items4 = [
            ['product_id' => 9, 'quantity' => 7, 'unit_price' => 490.00],
            ['product_id' => 10, 'quantity' => 16, 'unit_price' => 125.75],
        ];

        $total4 = 0;
        foreach ($items4 as $item) {
            $purchase4->items()->create($item);

            $product = Product::find($item['product_id']);
            $currentStock = $product->stock;
            $currentCost = (float) $product->average_cost;
            $newQuantity = $item['quantity'];
            $newPrice = (float) $item['unit_price'];

            if ($currentStock + $newQuantity > 0) {
                $newAverageCost = (($currentCost * $currentStock) + ($newPrice * $newQuantity))
                    / ($currentStock + $newQuantity);
            } else {
                $newAverageCost = $newPrice;
            }

            $product->update([
                'stock' => $currentStock + $newQuantity,
                'average_cost' => $newAverageCost,
            ]);

            $total4 += $newPrice * $newQuantity;
        }

        $purchase4->update(['total_amount' => $total4]);

        $purchase5 = Purchase::create([
            'supplier' => 'Eletrônicos Prime Ltda',
            'total_amount' => 0,
        ]);

        $items5 = [
            ['product_id' => 1, 'quantity' => 5, 'unit_price' => 160.00],
            ['product_id' => 3, 'quantity' => 12, 'unit_price' => 390.00],
            ['product_id' => 5, 'quantity' => 9, 'unit_price' => 455.50],
        ];

        $total5 = 0;
        foreach ($items5 as $item) {
            $purchase5->items()->create($item);

            $product = Product::find($item['product_id']);
            $currentStock = $product->stock;
            $currentCost = (float) $product->average_cost;
            $newQuantity = $item['quantity'];
            $newPrice = (float) $item['unit_price'];

            if ($currentStock + $newQuantity > 0) {
                $newAverageCost = (($currentCost * $currentStock) + ($newPrice * $newQuantity))
                    / ($currentStock + $newQuantity);
            } else {
                $newAverageCost = $newPrice;
            }

            $product->update([
                'stock' => $currentStock + $newQuantity,
                'average_cost' => $newAverageCost,
            ]);

            $total5 += $newPrice * $newQuantity;
        }

        $purchase5->update(['total_amount' => $total5]);
    }
}
