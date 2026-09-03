<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Services\InventoryService;
use App\Services\POSService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class POSInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $inventoryService;
    protected $posService;
    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryService = new InventoryService();
        $this->posService = new POSService($this->inventoryService);

        // Seed product category and a product with stock = 10
        $this->category = ProductCategory::create([
            'name' => 'Farm Produce',
            'slug' => 'farm-produce',
        ]);

        $this->product = Product::create([
            'product_category_id' => $this->category->id,
            'sku' => 'TEST-SKU',
            'name' => 'Test Mangoes',
            'unit' => 'kg',
            'selling_price' => 3000.00,
            'cost_price' => 1000.00,
            'stock' => 10,
            'low_stock_threshold' => 3,
            'active' => true,
        ]);

        // Opening movement
        InventoryMovement::create([
            'product_id' => $this->product->id,
            'type' => 'opening',
            'quantity' => 10,
            'reason' => 'Initial stock',
        ]);
    }

    /**
     * Test stock decrements correctly on successful sale.
     */
    public function test_stock_decrements_on_successful_sale()
    {
        $saleData = [
            'category' => 'product',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                ]
            ],
            'amount_paid' => 12000.00,
            'payment_method' => 'cash',
        ];

        $sale = $this->posService->createSale($saleData);

        $this->assertNotNull($sale);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals(12000.00, $sale->total);

        // Verify product stock is now 6 (10 - 4)
        $this->product = $this->product->fresh();
        $this->assertEquals(6, $this->product->stock);

        // Verify movement logs
        $movements = InventoryMovement::where('product_id', $this->product->id)->get();
        $this->assertCount(2, $movements); // 1 opening, 1 sale_out
        $this->assertEquals(-4, $movements->last()->quantity);
        $this->assertEquals('sale_out', $movements->last()->type);
    }

    /**
     * Test sale fails and rolls back when stock is insufficient.
     */
    public function test_sale_fails_when_stock_is_insufficient()
    {
        $saleData = [
            'category' => 'product',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15, // more than stock of 10
                ]
            ],
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock for product 'Test Mangoes'. Available: 10, requested adjustment: -15");

        $this->posService->createSale($saleData);

        // Verify stock remains 10
        $this->product = $this->product->fresh();
        $this->assertEquals(10, $this->product->stock);
    }

    /**
     * Test stock is restored on sale cancellation.
     */
    public function test_stock_restored_on_sale_cancellation()
    {
        $saleData = [
            'category' => 'product',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                ]
            ],
        ];

        $sale = $this->posService->createSale($saleData);
        $this->product = $this->product->fresh();
        $this->assertEquals(7, $this->product->stock);

        // Cancel sale
        $this->posService->cancelSale($sale, null, 'Customer changed mind');

        $sale = $sale->fresh();
        $this->assertEquals('cancelled', $sale->status);

        // Check stock restored to 10
        $this->product = $this->product->fresh();
        $this->assertEquals(10, $this->product->stock);

        // Check payment updated to refunded
        $payment = Payment::where('sale_id', $sale->id)->first();
        $this->assertEquals('refunded', $payment->status);
    }
}
