<?php

namespace App\Modules\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'total_profit' => number_format((float) $this->total_profit, 2, '.', ''),
            'items_count' => $this->items_count,
            'created_at' => $this->created_at,
        ];
    }
}
