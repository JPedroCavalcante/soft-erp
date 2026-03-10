<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const CACHE_TTL = 300;
    private const CACHE_KEY = 'dashboard.metrics';

    public function __construct(
        private readonly DashboardRepository $repository
    ) {}

    public function getMetrics(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => [
                'sales_this_month' => $this->repository->getSalesThisMonth(),
                'profit_this_month' => $this->repository->getProfitThisMonth(),
                'low_stock_products' => $this->repository->getLowStockProducts(),
                'top_selling_products' => $this->repository->getTopSellingProducts(),
                'sales_comparison' => $this->getSalesComparison(),
                'total_stock_value' => $this->repository->getTotalStockValue(),
            ]
        );
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getSalesComparison(): array
    {
        $today = $this->repository->getSalesToday();
        $yesterday = $this->repository->getSalesYesterday();

        $changePercentage = $yesterday > 0
            ? (($today - $yesterday) / $yesterday) * 100
            : 0;

        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'change_percentage' => round($changePercentage, 2),
        ];
    }
}
