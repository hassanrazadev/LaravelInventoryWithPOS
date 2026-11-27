<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseFormRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read expense', only: ['index', 'show']),
            new Middleware('permission:create expense', only: ['create', 'store']),
            new Middleware('permission:update expense', only: ['edit', 'update']),
            new Middleware('permission:delete expense', only: ['destroy', 'deleteOrRestore']),
        ];
    }

    public function index(Request $request): View
    {
        $categoryFilter = trim((string) $request->query('category', '')) ?: null;

        $expenses = Expense::withTrashed()
            ->with(['createdBy' => fn ($q) => $q->withTrashed()])
            ->when($categoryFilter, fn ($q) => $q->where('category', $categoryFilter))
            ->latest()
            ->get();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $activeCount = Expense::count();

        $stats = [
            'total_expenses' => (float) Expense::sum('expense_amount'),
            'month_expenses' => (float) Expense::where(function ($q) use ($monthStart, $monthEnd): void {
                $q->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->orWhere(function ($sub) use ($monthStart, $monthEnd): void {
                        $sub->whereNull('expense_date')->whereBetween('created_at', [$monthStart, $monthEnd]);
                    });
            })->sum('expense_amount'),
            'active_count' => $activeCount,
            'average_expense' => $activeCount > 0 ? (float) Expense::avg('expense_amount') : 0.0,
        ];

        $categories = Expense::CATEGORIES;

        return view('expenses.index', compact('expenses', 'stats', 'categories', 'categoryFilter'));
    }

    public function create(): View
    {
        $categories = Expense::CATEGORIES;

        return view('expenses.create', compact('categories'));
    }

    public function store(ExpenseFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['expense_date'] = $validated['expense_date'] ?? now()->toDateString();

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('status', true)
            ->with('message', 'New expense was recorded.');
    }

    public function show(int $id): View
    {
        $expense = Expense::withTrashed()
            ->with([
                'createdBy' => fn ($q) => $q->withTrashed(),
                'updatedBy' => fn ($q) => $q->withTrashed(),
            ])
            ->findOrFail($id);

        return view('expenses.show', compact('expense'));
    }

    public function edit(int $id): View
    {
        $expense = Expense::withTrashed()->findOrFail($id);
        $categories = Expense::CATEGORIES;

        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(ExpenseFormRequest $request, int $id): RedirectResponse
    {
        $expense = Expense::withTrashed()->findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('status', true)
            ->with('message', 'Expense was updated.');
    }

    public function deleteOrRestore(int $id): JsonResponse
    {
        $expense = Expense::withTrashed()->findOrFail($id);

        if ($expense->trashed()) {
            $expense->restore();
        } else {
            $expense->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Expense status was updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $expense = Expense::withTrashed()->findOrFail($id);
        $expense->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Expense was deleted.',
        ]);
    }
}
