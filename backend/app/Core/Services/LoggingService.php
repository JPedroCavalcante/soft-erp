<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    public static function logBusinessOperation(
        string $operation,
        string $description,
        array $context = [],
        string $level = 'info'
    ): void {
        $logData = [
            'operation' => $operation,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toISOString(),
            'context' => $context,
        ];

        Log::channel('repository')->$level($description, $logData);
    }

    public static function logSaleCreated(
        int $saleId,
        string $customer,
        float $totalAmount,
        float $totalProfit,
        int $itemsCount
    ): void {
        self::logBusinessOperation(
            'sale_created',
            sprintf('Sale #%d created for %s', $saleId, $customer),
            [
                'sale_id' => $saleId,
                'customer' => $customer,
                'total_amount' => $totalAmount,
                'total_profit' => $totalProfit,
                'items_count' => $itemsCount,
                'profit_margin' => $totalAmount > 0 ? ($totalProfit / $totalAmount) * 100 : 0,
            ]
        );
    }

    public static function logSaleCanceled(
        int $saleId,
        string $customer,
        float $totalAmount
    ): void {
        self::logBusinessOperation(
            'sale_canceled',
            sprintf('Sale #%d canceled for %s', $saleId, $customer),
            [
                'sale_id' => $saleId,
                'customer' => $customer,
                'total_amount' => $totalAmount,
            ],
            'warning'
        );
    }

    public static function logPurchaseCreated(
        int $purchaseId,
        string $supplier,
        float $totalAmount,
        int $itemsCount
    ): void {
        self::logBusinessOperation(
            'purchase_created',
            sprintf('Purchase #%d created from %s', $purchaseId, $supplier),
            [
                'purchase_id' => $purchaseId,
                'supplier' => $supplier,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
            ]
        );
    }

    public static function logInsufficientStock(
        int $productId,
        string $productName,
        int $available,
        int $requested
    ): void {
        self::logBusinessOperation(
            'insufficient_stock',
            sprintf('Insufficient stock for %s: available %d, requested %d', $productName, $available, $requested),
            [
                'product_id' => $productId,
                'product_name' => $productName,
                'available_stock' => $available,
                'requested_quantity' => $requested,
                'shortage' => $requested - $available,
            ],
            'warning'
        );
    }

    public static function logAverageCostUpdated(
        int $productId,
        string $productName,
        float $oldCost,
        float $newCost,
        int $oldStock,
        int $newStock
    ): void {
        self::logBusinessOperation(
            'average_cost_updated',
            sprintf('Average cost updated for %s', $productName),
            [
                'product_id' => $productId,
                'product_name' => $productName,
                'old_cost' => number_format($oldCost, 2),
                'new_cost' => number_format($newCost, 2),
                'cost_change' => number_format($newCost - $oldCost, 2),
                'cost_change_percent' => $oldCost > 0 ? (($newCost - $oldCost) / $oldCost) * 100 : 0,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'stock_change' => $newStock - $oldStock,
            ]
        );
    }
}
