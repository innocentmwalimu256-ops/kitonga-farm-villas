<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\POSService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class POSController extends Controller
{
    protected $posService;

    public function __construct(POSService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Show POS checkout screen.
     */
    public function terminal()
    {
        abort_if(!auth()->user()->hasPermissionTo('create_sales'), 403, 'Unauthorized to access POS terminal.');

        $products = Product::where('active', true)->with('category')->get();
        $categories = ProductCategory::all();
        $customers = Customer::all();

        return Inertia::render('Admin/POS/Terminal', [
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
        ]);
    }

    /**
     * Store POS sale.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('create_sales'), 403, 'Unauthorized to process sales.');

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|required_without:customer_id|string|max:255',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'category' => 'required|string|in:tour,product,bar,other',
            'discount' => 'nullable|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.service_name' => 'nullable|required_without:items.*.product_id|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_override' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        try {
            $sale = $this->posService->createSale($validated);
            return redirect()->route('admin.pos.receipt', $sale->id)
                ->with('success', 'Sale completed successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show printable receipt of the sale.
     */
    public function receipt($id)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'create_sales']), 403, 'Unauthorized to view sale receipts.');

        $sale = Sale::with(['items.product', 'customer', 'creator', 'payments'])->findOrFail($id);

        return Inertia::render('Admin/POS/Receipt', [
            'sale' => $sale,
        ]);
    }

    /**
     * List all past sales.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'create_sales']), 403, 'Unauthorized to view past sales.');

        $query = Sale::with(['customer', 'creator']);

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Admin/POS/Index', [
            'sales' => $sales,
            'filters' => $request->only(['category', 'status']),
        ]);
    }

    /**
     * Cancel / refund sale.
     */
    public function cancel(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('refund_sales'), 403, 'Unauthorized to refund or cancel sales.');

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $sale = Sale::findOrFail($id);

        try {
            $this->posService->cancelSale($sale, auth()->id(), $request->input('reason'));
            return back()->with('success', 'Sale cancelled and inventory restored successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
