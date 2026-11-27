<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\Supplier;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totalRevenue = (float) Order::where('status', Order::STATUS_COMPLETED)->sum('total');
        $todayRevenue = (float) Order::where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', today())
            ->sum('total');
        $totalPurchases = (float) Purchase::sum('total');
        $totalExpenses = (float) Expense::sum('expense_amount');
        $totalReturns = (float) SaleReturn::sum('return_price');

        $kpis = [
            'total_revenue' => $totalRevenue,
            'today_revenue' => $todayRevenue,
            'completed_orders_count' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'today_orders_count' => Order::where('status', Order::STATUS_COMPLETED)->whereDate('created_at', today())->count(),
            'total_purchases' => $totalPurchases,
            'purchases_count' => Purchase::count(),
            'suppliers_count' => Supplier::count(),
            'customers_count' => Customer::count(),
            'total_expenses' => $totalExpenses,
            'expenses_count' => Expense::count(),
            'total_returns' => $totalReturns,
            'returns_count' => SaleReturn::count(),
            'total_products' => Product::count(),
            'total_stock_units' => (int) Product::sum('quantity'),
            'low_stock_count' => Product::where('quantity', '<=', 5)->count(),
            'out_of_stock_count' => Product::where('quantity', '<=', 0)->count(),
            'net_cash_flow' => round($totalRevenue - $totalPurchases - $totalExpenses - $totalReturns, 2),
        ];

        $startDate = now()->subDays(6)->startOfDay();
        $ordersByDay = Order::where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', $startDate)
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m-d'));

        $purchasesByDay = Purchase::where('created_at', '>=', $startDate)
            ->get(['total', 'created_at'])
            ->groupBy(fn (Purchase $purchase): string => $purchase->created_at->format('Y-m-d'));

        $labels = [];
        $salesSeries = [];
        $purchasesSeries = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->format('Y-m-d');
            $labels[] = $day->format('M d');
            $salesSeries[] = round((float) ($ordersByDay->get($key)?->sum('total') ?? 0), 2);
            $purchasesSeries[] = round((float) ($purchasesByDay->get($key)?->sum('total') ?? 0), 2);
        }

        $chartSeries = [
            'labels' => $labels,
            'sales' => $salesSeries,
            'purchases' => $purchasesSeries,
            'seven_day_sales' => round(array_sum($salesSeries), 2),
            'seven_day_purchases' => round(array_sum($purchasesSeries), 2),
        ];

        $completedByMethod = Order::where('status', Order::STATUS_COMPLETED)
            ->get(['payment_method', 'total'])
            ->groupBy('payment_method');

        $palette = ['#4f46e5', '#10b981', '#0ea5e9', '#f59e0b'];
        $paymentRows = [];
        $idx = 0;

        foreach (Order::PAYMENT_METHODS as $key => $label) {
            $group = $completedByMethod->get($key);
            $amount = round((float) ($group?->sum('total') ?? 0), 2);
            $count = $group ? $group->count() : 0;
            if ($count > 0) {
                $paymentRows[] = [
                    'label' => $label,
                    'total' => $amount,
                    'count' => $count,
                    'color' => $palette[$idx % count($palette)],
                ];
            }
            $idx++;
        }

        $paymentBreakdown = [
            'rows' => $paymentRows,
            'labels' => array_column($paymentRows, 'label'),
            'totals' => array_column($paymentRows, 'total'),
            'colors' => array_column($paymentRows, 'color'),
        ];

        $recentOrders = Order::with([
            'customer' => fn ($q) => $q->withTrashed(),
            'createdBy' => fn ($q) => $q->withTrashed(),
        ])->latest()->limit(6)->get();

        $lowStockProducts = Product::with(['category' => fn ($q) => $q->withTrashed()])
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->orderBy('product_name')
            ->limit(6)
            ->get();

        $topRows = OrderDetail::query()
            ->selectRaw('product_id, SUM(quantity) as units_sold, SUM(sub_total) as total_revenue')
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
            ->groupBy('product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $topProductsMap = Product::withTrashed()
            ->with(['category' => fn ($q) => $q->withTrashed()])
            ->whereIn('id', $topRows->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $topSellingProducts = $topRows->map(function ($row) use ($topProductsMap): array {
            $product = $topProductsMap->get($row->product_id);

            return [
                'id' => $row->product_id,
                'name' => $product ? $product->product_name : 'Product',
                'code' => $product ? $product->product_code : '-',
                'category' => $product && $product->category ? $product->category->category_name : '-',
                'image' => $product ? asset('storage/' . $product->product_image) : null,
                'units_sold' => (int) $row->units_sold,
                'total_revenue' => round((float) $row->total_revenue, 2),
            ];
        });

        return view('dashboard', compact(
            'kpis',
            'chartSeries',
            'paymentBreakdown',
            'recentOrders',
            'lowStockProducts',
            'topSellingProducts'
        ));
    }
}
