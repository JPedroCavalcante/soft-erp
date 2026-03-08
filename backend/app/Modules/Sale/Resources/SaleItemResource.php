<?php

namespace App\Modules\Sale\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', function () {
                return $this->product->name;
            }),
            'quantity' => $this->quantity,
            'unit_sale_price' => number_format((float) $this->unit_sale_price, 2, '.', ''),
            'historical_average_cost' => number_format((float) $this->historical_average_cost, 2, '.', ''),
            'profit' => number_format((float) $this->profit, 2, '.', ''),
        ];
    }
}
