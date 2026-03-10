<?php

namespace App\Modules\Sale\Services;

use App\Core\Services\LoggingService;
use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Sale\Repositories\SaleRepository;
use App\Modules\Sale\Models\Sale;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly SaleRepository $repository
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

    public function store(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = $this->repository->create([
                'customer' => $data['customer'],
                'total_amount' => 0,
                'total_profit' => 0,
            ]);

            $totalAmount = 0;
            $totalProfit = 0;

            foreach ($data['items'] as $item) {
                $totals = $this->processSaleItem($sale, $item);
                $totalAmount += $totals['amount'];
                $totalProfit += $totals['profit'];
            }

            $sale->update([
                'total_amount' => $totalAmount,
                'total_profit' => $totalProfit,
            ]);

            LoggingService::logSaleCreated(
                saleId: $sale->id,
                customer: $sale->customer,
                totalAmount: $totalAmount,
                totalProfit: $totalProfit,
                itemsCount: count($data['items'])
            );

            DashboardService::clearCache();

            return $sale->load('items.product');
        });
    }

    public function show(int $id): Sale
    {
        $sale = $this->repository->find($id);
        return $sale->load('items.product');
    }

    public function cancel(int $id): Sale
    {
        return DB::transaction(function () use ($id) {
            $sale = $this->repository->find($id);

            if ($sale->canceled_at) {
                throw new \Exception('Esta venda já foi cancelada.');
            }

            $sale->load('items');

            foreach ($sale->items as $item) {
                $product = $this->lockProduct($item->product_id);

                $product->update([
                    'stock' => $product->stock + $item->quantity,
                ]);
            }

            $sale->update([
                'canceled_at' => now(),
            ]);

            LoggingService::logSaleCanceled(
                saleId: $sale->id,
                customer: $sale->customer,
                totalAmount: $sale->total_amount
            );

            DashboardService::clearCache();

            return $sale->load('items.product');
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $sale = $this->repository->find($id);
            $sale->load('items');

            foreach ($sale->items as $item) {
                $product = $this->lockProduct($item->product_id);

                $product->update([
                    'stock' => $product->stock + $item->quantity,
                ]);
            }

            $this->repository->delete($id);
        });
    }

    private function processSaleItem(Sale $sale, array $item): array
    {
        $product = $this->lockProduct($item['product_id']);
        $this->validateStock($product, $item['quantity']);

        $historicalCost = (float) $product->average_cost;
        $profit = $this->calculateProfit($item['unit_sale_price'], $historicalCost, $item['quantity']);

        $sale->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_sale_price' => $item['unit_sale_price'],
            'historical_average_cost' => $historicalCost,
            'profit' => $profit,
        ]);

        $this->updateProductStock($product, $item['quantity']);

        return [
            'amount' => $item['unit_sale_price'] * $item['quantity'],
            'profit' => $profit,
        ];
    }

    private function lockProduct(int $productId): Product
    {
        return Product::where('id', $productId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function validateStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            LoggingService::logInsufficientStock(
                productId: $product->id,
                productName: $product->name,
                available: $product->stock,
                requested: $quantity
            );

            throw new \Exception("Estoque insuficiente para {$product->name}. Disponível: {$product->stock}, solicitado: {$quantity}");
        }
    }

    private function calculateProfit(float $salePrice, float $cost, int $quantity): float
    {
        return ($salePrice - $cost) * $quantity;
    }

    private function updateProductStock(Product $product, int $quantity): void
    {
        $product->update([
            'stock' => $product->stock - $quantity,
        ]);
    }
}
