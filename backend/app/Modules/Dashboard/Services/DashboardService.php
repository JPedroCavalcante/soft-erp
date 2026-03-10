<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repository
    ) {}

    public function getMetrics(): array
    {
        return [
            'sales_this_month' => $this->repository->getSalesThisMonth(),
            'profit_this_month' => $this->repository->getProfitThisMonth(),
            'low_stock_products' => $this->repository->getLowStockProducts(),
            'top_selling_products' => $this->repository->getTopSellingProducts(),
            'sales_comparison' => $this->getSalesComparison(),
            'total_stock_value' => $this->repository->getTotalStockValue(),
        ];
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
