<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class ProductController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of products with categories.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'adjust_inventory']), 403, 'Unauthorized access to products registry.');

        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->input('category_id'));
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = ProductCategory::all();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('adjust_inventory'), 403, 'Unauthorized to create products.');

        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'unit' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        $initialStock = $validated['initial_stock'] ?? $validated['stock'] ?? 0;
        unset($validated['initial_stock']);

        try {
            $product = Product::create(array_merge($validated, ['stock' => 0, 'active' => true]));

            // Add initial stock movement
            if ($initialStock > 0) {
                $this->inventoryService->adjustStock(
                    $product->id,
                    $initialStock,
                    'opening',
                    'Initial stock seeding on product creation',
                    auth()->id()
                );
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update product details.
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('adjust_inventory'), 403, 'Unauthorized to update product details.');

        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'unit' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'active' => 'required|boolean',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product details updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('adjust_inventory'), 403, 'Unauthorized to delete products.');

        $product = Product::findOrFail($id);

        try {
            // Check if product has sales records
            $hasSales = \App\Models\SaleItem::where('product_id', $product->id)->exists();
            if ($hasSales) {
                $product->update(['active' => false]);
                return redirect()->route('admin.products.index')
                    ->with('success', 'Product has existing sales history, so it was marked inactive instead of permanent deletion.');
            }

            // Clean up inventory movements before deleting product
            \App\Models\InventoryMovement::where('product_id', $product->id)->delete();
            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Could not delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Process manual stock adjustments.
     */
    public function adjust(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('adjust_inventory'), 403, 'Unauthorized to adjust inventory levels.');

        $validated = $request->validate([
            'quantity' => 'required|integer', // positive for add, negative for remove
            'type' => 'required|string|in:stock_in,return,wastage,adjustment',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->inventoryService->adjustStock(
                $id,
                $validated['quantity'],
                $validated['type'],
                $validated['reason'],
                auth()->id()
            );

            return back()->with('success', 'Stock adjusted successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show movement history logs for a product.
     */
    public function movements($id)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'adjust_inventory']), 403, 'Unauthorized access to inventory movements log.');

        $product = Product::findOrFail($id);
        $movements = InventoryMovement::where('product_id', $product->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Products/Movements', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }
}
