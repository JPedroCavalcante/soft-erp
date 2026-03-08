<?php

namespace App\Modules\Sale\Services;

use App\Modules\Sale\Repositories\SaleRepository;
use App\Modules\Sale\Models\Sale;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly SaleRepository $repository
    ) {
    }

    public function index(): Collection
    {
        return $this->repository->all();
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
                $product = Product::findOrFail($item['product_id']);

                // VALIDAÇÃO CRÍTICA: estoque insuficiente
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para {$product->name}. Disponível: {$product->stock}, solicitado: {$item['quantity']}");
                }

                // Capturar custo médio histórico NO MOMENTO da venda
                $historicalCost = (float) $product->average_cost;

                // Calcular lucro do item (pode ser negativo se venda abaixo do custo)
                $profit = ($item['unit_sale_price'] - $historicalCost) * $item['quantity'];

                // Criar item da venda
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_sale_price' => $item['unit_sale_price'],
                    'historical_average_cost' => $historicalCost,
                    'profit' => $profit,
                ]);

                // IMPORTANTE: Decrementar stock mas NÃO alterar average_cost
                // (average_cost só muda em compras, não em vendas)
                $product->update([
                    'stock' => $product->stock - $item['quantity'],
                ]);

                $totalAmount += $item['unit_sale_price'] * $item['quantity'];
                $totalProfit += $profit;
            }

            $sale->update([
                'total_amount' => $totalAmount,
                'total_profit' => $totalProfit,
            ]);

            return $sale->load('items.product');
        });
    }

    public function show(int $id): Sale
    {
        $sale = $this->repository->find($id);
        return $sale->load('items.product');
    }
}
