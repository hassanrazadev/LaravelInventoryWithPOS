<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierFormRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read supplier', only: ['index', 'show']),
            new Middleware('permission:create supplier', only: ['create', 'store']),
            new Middleware('permission:update supplier', only: ['edit', 'update']),
            new Middleware('permission:delete supplier', only: ['destroy', 'deleteOrRestore']),
        ];
    }

    public function index(): View
    {
        $suppliers = Supplier::withTrashed()
            ->withCount('purchases')
            ->with('createdBy')
            ->latest()
            ->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SupplierFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('status', true)
            ->with('message', 'New supplier was added.');
    }

    public function show(int $id): View
    {
        $supplier = Supplier::withTrashed()
            ->with([
                'createdBy' => fn ($q) => $q->withTrashed(),
                'updatedBy' => fn ($q) => $q->withTrashed(),
                'purchases' => fn ($q) => $q->withTrashed()->with(['createdBy' => fn ($u) => $u->withTrashed()])->latest(),
            ])
            ->findOrFail($id);

        $totalSpent = (float) $supplier->purchases->whereNull('deleted_at')->sum('total');

        return view('suppliers.show', compact('supplier', 'totalSpent'));
    }

    public function edit(int $id): View
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierFormRequest $request, int $id): RedirectResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('status', true)
            ->with('message', 'Supplier was updated.');
    }

    public function deleteOrRestore(int $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        if ($supplier->trashed()) {
            $supplier->restore();
        } else {
            $supplier->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Supplier status was updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        if ($supplier->purchases()->withTrashed()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot permanently delete a supplier with existing purchase records. Deactivate instead.',
            ]);
        }

        $supplier->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Supplier was deleted.',
        ]);
    }
}
