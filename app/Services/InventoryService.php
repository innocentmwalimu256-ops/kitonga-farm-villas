<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class InventoryService
{
    /**
     * Record a stock movement and adjust product stock level inside a transaction.
     */
    public function adjustStock(
        int $productId,
        int $quantity,
        string $type,
        ?string $reason = null,
        ?int $userId = null,
        ?string $refType = null,
        ?int $refId = null
    ): Product {
        return DB::transaction(function () use ($productId, $quantity, $type, $reason, $userId, $refType, $refId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $oldStock = $product->stock;

            // Determine stock change direction
            // types: opening, stock_in, sale_out, return, wastage, adjustment
            $change = 0;
            switch ($type) {
                case 'opening':
                case 'stock_in':
                case 'return':
                    $change = abs($quantity);
                    break;
                case 'sale_out':
                case 'wastage':
                    $change = -abs($quantity);
                    break;
                case 'adjustment':
                    $change = $quantity; // can be positive or negative
                    break;
                default:
                    throw new Exception("Invalid stock movement type: {$type}");
            }

            $newStock = $oldStock + $change;

            if ($newStock < 0) {
                throw new Exception("Insufficient stock for product '{$product->name}'. Available: {$oldStock}, requested adjustment: {$change}");
            }

            // 1. Update product stock level
            $product->update([
                'stock' => $newStock,
            ]);

            // 2. Create inventory movement log
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $change,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'reason' => $reason,
                'created_by' => $userId,
            ]);

            // 3. Create Audit Log for manual adjustments
            if ($type === 'adjustment' || $type === 'wastage') {
                ActivityLog::create([
                    'user_id' => $userId,
                    'action' => 'inventory_adjusted',
                    'entity_type' => 'Product',
                    'entity_id' => $product->id,
                    'old_values' => ['stock' => $oldStock],
                    'new_values' => ['stock' => $newStock],
                    'metadata' => [
                        'type' => $type,
                        'reason' => $reason,
                        'change' => $change,
                    ],
                    'created_at' => Carbon::now(),
                ]);
            }

            return $product;
        });
    }
}
