<?php

namespace App\Modules\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMetricsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sales_this_month' => number_format((float) $this->resource['sales_this_month'], 2, '.', ''),
            'profit_this_month' => number_format((float) $this->resource['profit_this_month'], 2, '.', ''),
            'low_stock_products' => $this->resource['low_stock_products'],
            'top_selling_products' => $this->resource['top_selling_products'],
            'sales_comparison' => [
                'today' => number_format((float) $this->resource['sales_comparison']['today'], 2, '.', ''),
                'yesterday' => number_format((float) $this->resource['sales_comparison']['yesterday'], 2, '.', ''),
                'change_percentage' => $this->resource['sales_comparison']['change_percentage'],
            ],
            'total_stock_value' => number_format((float) $this->resource['total_stock_value'], 2, '.', ''),
        ];
    }
}
