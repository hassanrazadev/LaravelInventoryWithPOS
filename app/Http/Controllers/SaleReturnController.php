<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleReturnFormRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SaleReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleReturnController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read return', only: ['index', 'show']),
            new Middleware('permission:create return', only: ['create', 'store', 'orderItems']),
            new Middleware('permission:update return', only: ['edit', 'update']),
            new Middleware('permission:delete return', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $returns = SaleReturn::with([
            'order' => fn ($q) => $q->withTrashed(),
            'product' => fn ($q) => $q->withTrashed(),
            'createdBy' => fn ($q) => $q->withTrashed(),
        ])->latest()->get();

        $stats = [
            'total_returns' => $returns->count(),
            'total_refunded' => (float) $returns->sum('return_price'),
            'units_restocked' => (int) $returns->where('restock', true)->sum('quantity'),
            'units_not_restocked' => (int) $returns->where('restock', false)->sum('quantity'),
        ];

        return view('returns.index', compact('returns', 'stats'));
    }

    public function create(Request $request): View
    {
        $orders = Order::where('status', Order::STATUS_COMPLETED)
            ->with(['customer' => fn ($q) => $q->withTrashed()])
            ->latest()
            ->get();

        $selectedOrderId = $request->query('order_id');

        return view('returns.create', compact('orders', 'selectedOrderId'));
    }

    public function orderItems(int $orderId): JsonResponse
    {
        $order = Order::with([
            'orderDetails.product' => fn ($q) => $q->withTrashed(),
        ])->findOrFail($orderId);

        if ($order->status !== Order::STATUS_COMPLETED) {
            return response()->json([
                'status' => false,
                'message' => 'Only completed orders can be returned.',
                'items' => [],
            ], 422);
        }

        $returnedByProduct = SaleReturn::where('order_id', $order->id)
            ->get()
            ->groupBy('product_id')
            ->map(fn ($group): int => (int) $group->sum('quantity'));

        $items = $order->orderDetails->map(function (OrderDetail $detail) use ($returnedByProduct): array {
            $returnedQty = (int) ($returnedByProduct->get($detail->product_id) ?? 0);
            $remainingQty = max(0, $detail->quantity - $returnedQty);

            return [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product ? $detail->product->product_name : 'Product',
                'product_code' => $detail->product ? $detail->product->product_code : '-',
                'purchased_qty' => (int) $detail->quantity,
                'returned_qty' => $returnedQty,
                'remaining_qty' => $remainingQty,
                'unit_price' => round((float) $detail->unit_price, 2),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'order_no' => $order->order_no,
            'items' => $items,
        ]);
    }

    public function store(SaleReturnFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $restock = $request->has('restock') ? $request->boolean('restock') : true;

        DB::transaction(function () use ($validated, $restock): void {
            $order = Order::lockForUpdate()->findOrFail((int) $validated['order_id']);

            if ($order->status !== Order::STATUS_COMPLETED) {
                throw ValidationException::withMessages([
                    'order_id' => 'Returns can only be recorded for completed orders.',
                ]);
            }

            $productId = (int) $validated['product_id'];
            $detail = OrderDetail::where('order_id', $order->id)
                ->where('product_id', $productId)
                ->first();

            if (! $detail) {
                throw ValidationException::withMessages([
                    'product_id' => 'The selected product was not part of this order.',
                ]);
            }

            $alreadyReturned = (int) SaleReturn::where('order_id', $order->id)
                ->where('product_id', $productId)
                ->sum('quantity');

            $remaining = $detail->quantity - $alreadyReturned;
            $qty = (int) $validated['quantity'];

            if ($qty > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$remaining} unit(s) remaining returnable for this item.",
                ]);
            }

            $hasExplicitPrice = array_key_exists('return_price', $validated)
                && $validated['return_price'] !== null
                && $validated['return_price'] !== '';

            $returnPrice = $hasExplicitPrice
                ? round((float) $validated['return_price'], 2)
                : round((float) $detail->unit_price * $qty, 2);

            SaleReturn::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $qty,
                'return_price' => $returnPrice,
                'reason' => $validated['reason'] ?? null,
                'restock' => $restock,
                'created_by' => auth()->id(),
            ]);

            if ($restock) {
                $product = Product::withTrashed()->lockForUpdate()->findOrFail($productId);
                $product->increment('quantity', $qty);
            }
        });

        return redirect()->route('returns.index')
            ->with('status', true)
            ->with('message', 'Sales return was recorded.');
    }

    public function show(int $id): View
    {
        $saleReturn = SaleReturn::with([
            'order' => fn ($q) => $q->withTrashed()->with(['customer' => fn ($c) => $c->withTrashed()]),
            'product' => fn ($q) => $q->withTrashed(),
            'createdBy' => fn ($q) => $q->withTrashed(),
            'updatedBy' => fn ($q) => $q->withTrashed(),
        ])->findOrFail($id);

        return view('returns.show', compact('saleReturn'));
    }

    public function edit(int $id): View
    {
        $saleReturn = SaleReturn::with([
            'order' => fn ($q) => $q->withTrashed(),
            'product' => fn ($q) => $q->withTrashed(),
        ])->findOrFail($id);

        return view('returns.edit', compact('saleReturn'));
    }

    public function update(SaleReturnFormRequest $request, int $id): RedirectResponse
    {
        $saleReturn = SaleReturn::findOrFail($id);
        $validated = $request->validated();

        $saleReturn->update([
            'return_price' => round((float) $validated['return_price'], 2),
            'reason' => $validated['reason'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('returns.index')
            ->with('status', true)
            ->with('message', 'Sales return was updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        DB::transaction(function () use ($id): void {
            $saleReturn = SaleReturn::withTrashed()->lockForUpdate()->findOrFail($id);

            if ($saleReturn->restock) {
                $product = Product::withTrashed()->lockForUpdate()->find($saleReturn->product_id);
                if ($product) {
                    $product->update([
                        'quantity' => max(0, $product->quantity - $saleReturn->quantity),
                    ]);
                }
            }

            $saleReturn->forceDelete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Sales return was deleted.',
        ]);
    }
}
