<?php

namespace App\Modules\Sale\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'total_profit' => number_format((float) $this->total_profit, 2, '.', ''),
            'is_canceled' => $this->canceled_at !== null,
            'canceled_at' => $this->canceled_at,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
