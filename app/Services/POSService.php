<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class POSService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Process a new POS sale inside a transaction.
     * Decrements inventory and logs movements automatically.
     */
    public function createSale(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Resolve or create customer if provided
            $customerId = null;
            if (isset($data['customer_id'])) {
                $customerId = $data['customer_id'];
            } elseif (!empty($data['customer_name'])) {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            // Generate unique reference
            $reference = 'KFV-POS-' . Carbon::now()->format('ymd') . '-' . mt_rand(1000, 9999);

            // 2. Create Sale Header
            $sale = Sale::create([
                'reference' => $reference,
                'customer_id' => $customerId,
                'category' => $data['category'] ?? 'product', // tour, product, bar, other
                'subtotal' => 0.00,
                'discount' => $data['discount'] ?? 0.00,
                'total' => 0.00,
                'status' => 'completed',
                'created_by' => $data['user_id'] ?? null,
            ]);

            $subtotal = 0.00;

            // 3. Process Items
            foreach ($data['items'] as $item) {
                // If it is a product, lock and adjust stock
                $productId = $item['product_id'] ?? null;
                $serviceName = $item['service_name'] ?? null;
                $quantity = $item['quantity'] ?? 1;
                $unitPrice = $item['unit_price'] ?? 0.00;
                $costPrice = 0.00;
                $description = "";

                if ($productId) {
                    $product = Product::findOrFail($productId);
                    $costPrice = $product->cost_price;
                    $description = $product->name;
                    $unitPrice = $product->selling_price; // use default unless override exists

                    // Deduct stock immediately (this validates availability and throws error if insufficient)
                    $this->inventoryService->adjustStock(
                        $productId,
                        $quantity,
                        'sale_out',
                        "POS Sale: {$reference}",
                        $data['user_id'] ?? null,
                        'sales',
                        $sale->id
                    );
                } else {
                    $description = $serviceName ?? "Farm Service";
                }

                if (isset($item['unit_price_override'])) {
                    $unitPrice = $item['unit_price_override'];
                }

                $itemTotal = $unitPrice * $quantity;
                $subtotal += $itemTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'service_name' => $serviceName,
                    'description_snapshot' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_snapshot' => $costPrice,
                    'total' => $itemTotal,
                ]);
            }

            // Calculate totals
            $total = $subtotal - $sale->discount;
            $sale->update([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            // 4. Record Payment immediately if amount is supplied
            $amountPaid = $data['amount_paid'] ?? $total;
            if ($amountPaid > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => $data['payment_method'] ?? 'cash',
                    'reference' => $data['payment_reference'] ?? null,
                    'amount' => $amountPaid,
                    'status' => 'completed',
                    'paid_at' => Carbon::now(),
                    'recorded_by' => $data['user_id'] ?? null,
                ]);
            }

            // 5. Audit Logging
            ActivityLog::create([
                'user_id' => $data['user_id'] ?? null,
                'action' => 'pos_sale_created',
                'entity_type' => 'Sale',
                'entity_id' => $sale->id,
                'new_values' => $sale->toArray(),
                'metadata' => [
                    'item_count' => count($data['items']),
                    'paid' => $amountPaid,
                ],
                'created_at' => Carbon::now(),
            ]);

            return $sale;
        });
    }

    /**
     * Cancel and refund a sale, restoring stock if appropriate.
     */
    public function cancelSale(Sale $sale, ?int $userId, ?string $reason = null)
    {
        return DB::transaction(function () use ($sale, $userId, $reason) {
            if ($sale->status === 'cancelled') {
                throw new Exception("Sale is already cancelled.");
            }

            // 1. Update sale status
            $sale->update([
                'status' => 'cancelled',
            ]);

            // 2. Reverse inventory for products in the sale
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $this->inventoryService->adjustStock(
                        $item->product_id,
                        $item->quantity,
                        'return',
                        "Cancelled POS Sale: {$sale->reference}",
                        $userId,
                        'sales',
                        $sale->id
                    );
                }
            }

            // 3. Mark associated payments as refunded
            Payment::where('sale_id', $sale->id)->update([
                'status' => 'refunded',
            ]);

            // 4. Audit Log
            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'pos_sale_cancelled',
                'entity_type' => 'Sale',
                'entity_id' => $sale->id,
                'old_values' => ['status' => 'completed'],
                'new_values' => ['status' => 'cancelled'],
                'metadata' => ['reason' => $reason],
                'created_at' => Carbon::now(),
            ]);

            return $sale;
        });
    }
}
