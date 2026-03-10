<?php

namespace App\Modules\Dashboard\Repositories;

use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getSalesThisMonth(): float
    {
        return (float) Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
    }

    public function getProfitThisMonth(): float
    {
        return (float) Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_profit');
    }

    public function getLowStockProducts(): array
    {
        return Product::select('id', 'name', 'stock', 'average_cost')
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->get()
            ->toArray();
    }

    public function getTopSellingProducts(): array
    {
        return DB::table('sale_items')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity')
            )
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getSalesToday(): float
    {
        return (float) Sale::whereDate('created_at', now()->toDateString())
            ->sum('total_amount');
    }

    public function getSalesYesterday(): float
    {
        return (float) Sale::whereDate('created_at', now()->subDay()->toDateString())
            ->sum('total_amount');
    }

    public function getTotalStockValue(): float
    {
        $total = DB::table('products')
            ->selectRaw('SUM(stock * average_cost) as total_value')
            ->value('total_value');

        return (float) ($total ?? 0);
    }
}
