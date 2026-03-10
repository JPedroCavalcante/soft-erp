<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly int $available,
        public readonly int $requested,
        string $message = '',
    ) {
        $finalMessage = $message ?: "Estoque insuficiente para {$productName}. Disponível: {$available}, solicitado: {$requested}";
        parent::__construct($finalMessage, 422);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'insufficient_stock',
            'details' => [
                'product' => $this->productName,
                'available' => $this->available,
                'requested' => $this->requested,
            ],
        ], 422);
    }
}
