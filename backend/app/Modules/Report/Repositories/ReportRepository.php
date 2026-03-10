<?php

namespace App\Modules\Report\Repositories;

use Illuminate\Support\Facades\DB;

class ReportRepository
{
    public function getSalesReport(array $filters): array
    {
        $query = DB::table('sales')
            ->select(
                'sales.id',
                'sales.customer',
                'sales.total_amount',
                'sales.total_profit',
                'sales.created_at',
                DB::raw('COUNT(sale_items.id) as items_count')
            )
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->groupBy('sales.id', 'sales.customer', 'sales.total_amount', 'sales.total_profit', 'sales.created_at')
            ->orderByDesc('sales.created_at');

        if (isset($filters['start_date'])) {
            $query->whereDate('sales.created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('sales.created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['customer'])) {
            $query->where('sales.customer', 'like', '%' . $filters['customer'] . '%');
        }

        return $query->get()->toArray();
    }

    public function getPurchasesReport(array $filters): array
    {
        $query = DB::table('purchases')
            ->select(
                'purchases.id',
                'purchases.supplier',
                'purchases.total_amount',
                'purchases.created_at',
                DB::raw('COUNT(purchase_items.id) as items_count')
            )
            ->leftJoin('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->groupBy('purchases.id', 'purchases.supplier', 'purchases.total_amount', 'purchases.created_at')
            ->orderByDesc('purchases.created_at');

        if (isset($filters['start_date'])) {
            $query->whereDate('purchases.created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('purchases.created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['supplier'])) {
            $query->where('purchases.supplier', 'like', '%' . $filters['supplier'] . '%');
        }

        return $query->get()->toArray();
    }

    public function getProfitReport(array $filters): array
    {
        $query = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(sale_items.profit) as total_profit'),
                DB::raw('AVG(sale_items.unit_sale_price) as avg_sale_price'),
                DB::raw('AVG(sale_items.historical_average_cost) as avg_cost')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_profit');

        if (isset($filters['start_date'])) {
            $query->whereDate('sales.created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('sales.created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        return $query->get()->toArray();
    }

    public function getStockReport(): array
    {
        return DB::table('products')
            ->select(
                'id',
                'name',
                'stock',
                'average_cost',
                'sale_price',
                DB::raw('stock * average_cost as stock_value'),
                DB::raw('CASE
                    WHEN stock = 0 THEN "SEM ESTOQUE"
                    WHEN stock <= 10 THEN "ESTOQUE BAIXO"
                    WHEN stock <= 50 THEN "ESTOQUE NORMAL"
                    ELSE "ESTOQUE ALTO"
                END as stock_status')
            )
            ->orderBy('stock')
            ->get()
            ->toArray();
    }
}
