<?php

namespace App\Modules\Purchase\Services;

use App\Core\Services\LoggingService;
use App\Modules\Purchase\Repositories\PurchaseRepository;
use App\Modules\Purchase\Models\Purchase;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepository $repository
    ) {
    }

    public function index(int $perPage = 10)
    {
        return $this->repository->all($perPage);
    }

    public function all()
    {
        return $this->repository->allWithoutPagination();
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
                $totalAmount += $this->processPurchaseItem($purchase, $item);
            }

            $purchase->update([
                'total_amount' => $totalAmount,
            ]);

            LoggingService::logPurchaseCreated(
                purchaseId: $purchase->id,
                supplier: $purchase->supplier,
                totalAmount: $totalAmount,
                itemsCount: count($data['items'])
            );

            return $purchase->load('items.product');
        });
    }

    public function show(int $id): Purchase
    {
        $purchase = $this->repository->find($id);
        return $purchase->load('items.product');
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $purchase = $this->repository->find($id);
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                $product = $this->lockProduct($item->product_id);

                $product->update([
                    'stock' => $product->stock - $item->quantity,
                ]);
            }

            $this->repository->delete($id);
        });
    }

    private function processPurchaseItem(Purchase $purchase, array $item): float
    {
        $purchase->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
        ]);

        $product = $this->lockProduct($item['product_id']);
        $newPrice = (float) $item['unit_price'];
        $newQuantity = $item['quantity'];

        $this->updateProductOnPurchase($product, $newPrice, $newQuantity);

        return $newPrice * $newQuantity;
    }

    private function lockProduct(int $productId): Product
    {
        return Product::where('id', $productId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function calculateWeightedAverageCost(Product $product, float $newPrice, int $newQuantity): float
    {
        $currentStock = $product->stock;
        $currentCost = (float) $product->average_cost;

        if ($currentStock + $newQuantity > 0) {
            return ($currentCost * $currentStock + $newPrice * $newQuantity) / ($currentStock + $newQuantity);
        }

        return $newPrice;
    }

    private function updateProductOnPurchase(Product $product, float $newPrice, int $newQuantity): void
    {
        $oldCost = (float) $product->average_cost;
        $oldStock = $product->stock;

        $newAverageCost = $this->calculateWeightedAverageCost($product, $newPrice, $newQuantity);
        $newStock = $product->stock + $newQuantity;

        $product->update([
            'stock' => $newStock,
            'average_cost' => $newAverageCost,
        ]);

        LoggingService::logAverageCostUpdated(
            productId: $product->id,
            productName: $product->name,
            oldCost: $oldCost,
            newCost: $newAverageCost,
            oldStock: $oldStock,
            newStock: $newStock
        );
    }
}
