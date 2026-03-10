<?php

namespace App\Modules\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfitReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'total_quantity_sold' => (int) $this->total_quantity_sold,
            'total_profit' => number_format((float) $this->total_profit, 2, '.', ''),
            'avg_sale_price' => number_format((float) $this->avg_sale_price, 2, '.', ''),
            'avg_cost' => number_format((float) $this->avg_cost, 2, '.', ''),
        ];
    }
}
