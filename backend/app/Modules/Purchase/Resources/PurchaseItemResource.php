<?php

namespace App\Modules\Purchase\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
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
            'unit_price' => number_format((float) $this->unit_price, 2, '.', ''),
        ];
    }
}
