<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderFormRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\ThermalReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read order', only: ['index', 'show', 'receipt']),
            new Middleware('permission:create order', only: ['create', 'store', 'products']),
            new Middleware('permission:create order|read order', only: ['testThermalPrinter', 'printThermal']),
            new Middleware('permission:update order|delete order', only: ['cancel']),
            new Middleware('permission:delete order', only: ['destroy', 'deleteOrRestore']),
        ];
    }

    public function index(Request $request): View
    {
        $statusFilter = in_array($request->query('status'), [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)
            ? $request->query('status')
            : null;

        $orders = Order::withTrashed()
            ->with([
                'customer' => fn ($q) => $q->withTrashed(),
                'createdBy' => fn ($q) => $q->withTrashed(),
            ])
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest()
            ->get();

        $stats = [
            'total_orders' => Order::count(),
            'completed_revenue' => (float) Order::where('status', Order::STATUS_COMPLETED)->sum('total'),
            'today_revenue' => (float) Order::where('status', Order::STATUS_COMPLETED)->whereDate('created_at', today())->sum('total'),
            'cancelled_orders' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('orders.index', compact('orders', 'stats', 'statusFilter'));
    }

    public function create(): View
    {
        $categories = Category::withCount('products')
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::with(['category' => fn ($q) => $q->withTrashed()])
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product): array => $this->formatPosProduct($product))
            ->values();
        $paymentMethods = Order::PAYMENT_METHODS;
        $printerConfig = config('pos.printer');

        return view('orders.create', compact('categories', 'customers', 'products', 'paymentMethods', 'printerConfig'));
    }

    public function products(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $products = Product::with(['category' => fn ($q) => $q->withTrashed()])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product): array => $this->formatPosProduct($product))
            ->values();

        return response()->json([
            'status' => true,
            'products' => $products,
        ]);
    }

    public function store(OrderFormRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated): Order {
            $items = $validated['items'];
            $productIds = array_column($items, 'product_id');

            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalItems = 0;
            $subTotal = 0.0;
            $linePayloads = [];

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                $product = $lockedProducts->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected products is no longer available.',
                    ]);
                }

                if ($product->quantity < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->product_name} (available: {$product->quantity}).",
                    ]);
                }

                $unitPrice = round((float) $product->sale_price, 2);
                $lineSubTotal = round($unitPrice * $qty, 2);
                $totalItems += $qty;
                $subTotal = round($subTotal + $lineSubTotal, 2);

                $linePayloads[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'sub_total' => $lineSubTotal,
                ];
            }

            $discount = round((float) ($validated['discount'] ?? 0), 2);
            if ($discount > $subTotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount cannot exceed the subtotal.',
                ]);
            }

            $taxRate = round((float) ($validated['tax_rate'] ?? 0), 2);
            $taxable = round($subTotal - $discount, 2);
            $tax = round($taxable * ($taxRate / 100), 2);
            $total = round($taxable + $tax, 2);

            $paidAmount = round((float) $validated['paid_amount'], 2);
            if ($paidAmount + 0.00001 < $total) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount must be at least '.number_format($total, 2).'.',
                ]);
            }

            $changeAmount = round(max(0, $paidAmount - $total), 2);

            $order = Order::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'created_by' => auth()->id(),
                'order_no' => Order::generateOrderNumber(),
                'total_items' => $totalItems,
                'sub_total' => $subTotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => Order::STATUS_COMPLETED,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($linePayloads as $line) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'sub_total' => $line['sub_total'],
                ]);

                $line['product']->decrement('quantity', $line['quantity']);
            }

            return $order;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Order {$order->order_no} completed successfully.",
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'total' => number_format((float) $order->total, 2),
                    'paid_amount' => number_format((float) $order->paid_amount, 2),
                    'change_amount' => number_format((float) $order->change_amount, 2),
                    'total_items' => $order->total_items,
                ],
                'receipt_url' => route('orders.receipt', $order->id),
                'print_thermal_url' => route('orders.printThermal', $order->id),
                'show_url' => route('orders.show', $order->id),
            ], 201);
        }

        return redirect()->route('orders.show', $order->id)
            ->with('status', true)
            ->with('message', "Order {$order->order_no} completed successfully.");
    }

    public function show(int $id): View
    {
        $order = $this->findOrderWithRelations($id);

        return view('orders.show', compact('order'));
    }

    public function receipt(Request $request, int $id): View
    {
        $order = $this->findOrderWithRelations($id);
        $paperWidth = (int) $request->query('width', config('pos.printer.paper_width', 80)) === 58 ? 58 : 80;
        $printerConfig = config('pos.printer');

        return view('orders.receipt', compact('order', 'paperWidth', 'printerConfig'));
    }

    public function testThermalPrinter(Request $request, ThermalReceiptService $printerService): JsonResponse
    {
        $options = $this->validatePrinterOptions($request, $printerService);
        $result = $printerService->printTest($options);

        return response()->json($result);
    }

    public function printThermal(Request $request, int $id, ThermalReceiptService $printerService): JsonResponse
    {
        $order = $this->findOrderWithRelations($id);
        $options = $this->validatePrinterOptions($request, $printerService);
        $result = $printerService->printOrder($order, $options);

        return response()->json($result);
    }

    public function cancel(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = DB::transaction(function () use ($id): array {
            $order = Order::withTrashed()->lockForUpdate()->findOrFail($id);

            if ($order->status === Order::STATUS_CANCELLED) {
                return [
                    'status' => false,
                    'message' => 'Order is already cancelled.',
                    'order_no' => $order->order_no,
                ];
            }

            $details = OrderDetail::withTrashed()->where('order_id', $order->id)->get();
            foreach ($details as $detail) {
                Product::withTrashed()
                    ->where('id', $detail->product_id)
                    ->increment('quantity', $detail->quantity);
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'updated_by' => auth()->id(),
            ]);

            return [
                'status' => true,
                'message' => "Order {$order->order_no} was cancelled and stock was restored.",
                'order_no' => $order->order_no,
            ];
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
            ]);
        }

        return redirect()->back()
            ->with('status', $result['status'])
            ->with('message', $result['message']);
    }

    public function deleteOrRestore(int $id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);

        if ($order->trashed()) {
            $order->restore();
        } else {
            $order->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Order status was updated.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::transaction(function () use ($id): void {
            $order = Order::withTrashed()->lockForUpdate()->findOrFail($id);

            if ($order->status === Order::STATUS_COMPLETED) {
                $details = OrderDetail::withTrashed()->where('order_id', $order->id)->get();
                foreach ($details as $detail) {
                    Product::withTrashed()
                        ->where('id', $detail->product_id)
                        ->increment('quantity', $detail->quantity);
                }
            }

            $order->forceDelete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Order was deleted.',
        ]);
    }

    private function findOrderWithRelations(int $id): Order
    {
        return Order::withTrashed()
            ->with([
                'customer' => fn ($q) => $q->withTrashed(),
                'createdBy' => fn ($q) => $q->withTrashed(),
                'updatedBy' => fn ($q) => $q->withTrashed(),
                'orderDetails.product' => fn ($q) => $q->withTrashed(),
                'returns.product' => fn ($q) => $q->withTrashed(),
            ])
            ->findOrFail($id);
    }

    private function formatPosProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'category_id' => $product->category_id,
            'category_name' => $product->category ? $product->category->category_name : '-',
            'sale_price' => (float) $product->sale_price,
            'quantity' => (int) $product->quantity,
            'image' => asset('storage/'.$product->product_image),
        ];
    }

    private function validatePrinterOptions(Request $request, ThermalReceiptService $printerService): array
    {
        $validated = $request->validate([
            'driver' => ['nullable', 'string', Rule::in(['tcp', 'http'])],
            'host' => [
                'nullable',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($printerService): void {
                    if ($value !== null && $value !== '' && ! $printerService->isAllowedPrinterHost((string) $value)) {
                        $fail('Only localhost or private LAN IP printer hosts are allowed.');
                    }
                },
            ],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'http_path' => ['nullable', 'string', 'max:120'],
            'http_format' => ['nullable', 'string', Rule::in(['escpos', 'text'])],
            'paper_width' => ['nullable', 'integer', Rule::in([58, 80])],
            'open_cash_drawer' => ['nullable', 'boolean'],
        ]);

        return array_filter($validated, fn (mixed $v): bool => $v !== null && $v !== '');
    }
}
