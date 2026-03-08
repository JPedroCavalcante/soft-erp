<?php

namespace App\Modules\Purchase\Services;

use App\Modules\Purchase\Repositories\PurchaseRepository;
use App\Modules\Purchase\Models\Purchase;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepository $repository
    ) {
    }

    public function index(): Collection
    {
        return $this->repository->all();
    }

    public function store(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $purchase = $this->repository->create([
                'supplier' => $data['supplier'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                $product = Product::findOrFail($item['product_id']);

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

                $totalAmount += $newPrice * $newQuantity;
            }

            $purchase->update([
                'total_amount' => $totalAmount,
            ]);

            return $purchase->load('items.product');
        });
    }

    public function show(int $id): Purchase
    {
        $purchase = $this->repository->find($id);
        return $purchase->load('items.product');
    }
}
