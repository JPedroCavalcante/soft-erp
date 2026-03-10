<?php

namespace App\Modules\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock' => (int) $this->stock,
            'average_cost' => number_format((float) $this->average_cost, 2, '.', ''),
            'sale_price' => number_format((float) $this->sale_price, 2, '.', ''),
            'stock_value' => number_format((float) $this->stock_value, 2, '.', ''),
            'stock_status' => $this->stock_status,
        ];
    }
}
