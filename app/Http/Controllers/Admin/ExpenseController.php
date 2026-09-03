<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * List all expenses with filters.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_expenses'), 403, 'Unauthorized access to expenses log.');

        $query = Expense::with('creator');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        $categories = ['Farm', 'Food', 'Electricity', 'Water', 'Salaries', 'Maintenance', 'Transport', 'Supplies', 'Marketing', 'Other'];
        $paymentMethods = ['cash', 'mobile_money', 'bank_transfer', 'card', 'other'];

        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'payment_methods' => $paymentMethods,
            'filters' => $request->only(['category', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Store new expense.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_expenses'), 403, 'Unauthorized to create expenses.');

        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:5120', // Max 5MB
        ]);

        $attachmentPath = null;
        if ($request->hasFile('receipt')) {
            // Store receipt securely under private storage
            $attachmentPath = $request->file('receipt')->store('receipts');
        }

        $expense = Expense::create([
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'approved', // Defaults to approved in local tracking
            'created_by' => auth()->id(),
        ]);

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'expense_created',
            'entity_type' => 'Expense',
            'entity_id' => $expense->id,
            'new_values' => $expense->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Edit/Update expense.
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_expenses'), 403, 'Unauthorized to update expenses.');

        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:5120',
        ]);

        $oldValues = $expense->toArray();

        if ($request->hasFile('receipt')) {
            // Delete old file if exists
            if ($expense->attachment_path) {
                Storage::delete($expense->attachment_path);
            }
            $expense->attachment_path = $request->file('receipt')->store('receipts');
        }

        $expense->update([
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
        ]);

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'expense_updated',
            'entity_type' => 'Expense',
            'entity_id' => $expense->id,
            'old_values' => $oldValues,
            'new_values' => $expense->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Delete expense.
     */
    public function destroy($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_expenses'), 403, 'Unauthorized to delete expenses.');

        $expense = Expense::findOrFail($id);

        // Delete attachment from storage
        if ($expense->attachment_path) {
            Storage::delete($expense->attachment_path);
        }

        $oldValues = $expense->toArray();
        $expense->delete();

        // Audit Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'expense_deleted',
            'entity_type' => 'Expense',
            'entity_id' => $id,
            'old_values' => $oldValues,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /**
     * Update approval status of expense.
     */
    public function updateStatus(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('view_expenses'), 403, 'Unauthorized to approve or reject expenses.');

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $expense = Expense::findOrFail($id);
        $oldValues = $expense->toArray();

        $expense->update([
            'status' => $request->input('status'),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'expense_status_changed',
            'entity_type' => 'Expense',
            'entity_id' => $expense->id,
            'old_values' => $oldValues,
            'new_values' => $expense->fresh()->toArray(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', "Expense status updated to '{$expense->status}'.");
    }
}
