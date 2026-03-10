<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Resources\DashboardMetricsResource;
use App\Modules\Dashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service
    ) {}

    /**
     * Métricas do Dashboard
     *
     * Retorna todas as métricas principais do sistema em tempo real.
     *
     * @group Módulo de Dashboard
     *
     * @response 200 {
     *   "data": {
     *     "sales_this_month": "125000.50",
     *     "profit_this_month": "32000.00",
     *     "low_stock_products": [
     *       {"id": 1, "name": "Produto A", "stock": 5, "average_cost": "150.00"}
     *     ],
     *     "top_selling_products": [
     *       {"id": 1, "name": "Produto A", "total_quantity": 150}
     *     ],
     *     "sales_comparison": {
     *       "today": "5000.00",
     *       "yesterday": "4500.00",
     *       "change_percentage": 11.11
     *     },
     *     "total_stock_value": "850000.00"
     *   }
     * }
     */
    public function metrics(): DashboardMetricsResource
    {
        $metrics = $this->service->getMetrics();
        return new DashboardMetricsResource($metrics);
    }
}
