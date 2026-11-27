<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerFormRequest;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read customer', only: ['index', 'show']),
            new Middleware('permission:create customer', only: ['create', 'store']),
            new Middleware('permission:update customer', only: ['edit', 'update']),
            new Middleware('permission:delete customer', only: ['destroy', 'deleteOrRestore']),
        ];
    }

    public function index(): View
    {
        $customers = Customer::withTrashed()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', Order::STATUS_COMPLETED)], 'total')
            ->with('createdBy')
            ->latest()
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerFormRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'New customer was added.',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ],
            ], 201);
        }

        return redirect()->route('customers.index')
            ->with('status', true)
            ->with('message', 'New customer was added.');
    }

    public function show(int $id): View
    {
        $customer = Customer::withTrashed()
            ->with([
                'createdBy' => fn ($q) => $q->withTrashed(),
                'updatedBy' => fn ($q) => $q->withTrashed(),
                'orders' => fn ($q) => $q->withTrashed()->with(['createdBy' => fn ($u) => $u->withTrashed()])->latest(),
            ])
            ->findOrFail($id);

        $totalSpent = (float) $customer->orders
            ->whereNull('deleted_at')
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total');

        return view('customers.show', compact('customer', 'totalSpent'));
    }

    public function edit(int $id): View
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerFormRequest $request, int $id): RedirectResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('status', true)
            ->with('message', 'Customer was updated.');
    }

    public function deleteOrRestore(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            $customer->restore();
        } else {
            $customer->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer status was updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->orders()->withTrashed()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot permanently delete a customer with existing order records. Deactivate instead.',
            ]);
        }

        $customer->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Customer was deleted.',
        ]);
    }
}
