@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading ims-page-header mb-3">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Store Overview</li>
            </ol>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            @can('create expense')
                <a href="{{ route('expenses.create') }}" class="btn btn-light border">
                    <i class="fa fa-credit-card mr-1"></i> Record Expense
                </a>
            @endcan
            @can('create order')
                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                    <i class="fa fa-shopping-basket mr-1"></i> Open POS
                </a>
            @endcan
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
            <div class="ims-kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start" style="gap: 10px;">
                    <div style="min-width: 0;">
                        <div class="ims-kpi-label">Completed Sales</div>
                        <div class="ims-kpi-value text-primary">${{ number_format($kpis['total_revenue'], 2) }}</div>
                        <div class="ims-kpi-sub text-muted">
                            {{ $kpis['completed_orders_count'] }} orders · Today: ${{ number_format($kpis['today_revenue'], 2) }}
                        </div>
                    </div>
                    <span class="ims-kpi-icon ims-kpi-icon-primary"><i class="fa fa-shopping-basket"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
            <div class="ims-kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start" style="gap: 10px;">
                    <div style="min-width: 0;">
                        <div class="ims-kpi-label">Inventory Stock</div>
                        <div class="ims-kpi-value">{{ number_format($kpis['total_stock_units']) }} units</div>
                        <div class="ims-kpi-sub {{ $kpis['low_stock_count'] > 0 ? 'text-warning font-weight-semibold' : 'text-success' }}">
                            {{ $kpis['total_products'] }} products · {{ $kpis['low_stock_count'] }} low stock
                        </div>
                    </div>
                    <span class="ims-kpi-icon ims-kpi-icon-info"><i class="fa fa-cubes"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
            <div class="ims-kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start" style="gap: 10px;">
                    <div style="min-width: 0;">
                        <div class="ims-kpi-label">Total Outflow</div>
                        <div class="ims-kpi-value text-warning">${{ number_format($kpis['total_purchases'] + $kpis['total_expenses'], 2) }}</div>
                        <div class="ims-kpi-sub text-muted">
                            Purchases ${{ number_format($kpis['total_purchases'], 2) }} · Expenses ${{ number_format($kpis['total_expenses'], 2) }}
                        </div>
                    </div>
                    <span class="ims-kpi-icon ims-kpi-icon-warning"><i class="fa fa-credit-card"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="ims-kpi-card h-100">
                <div class="d-flex justify-content-between align-items-start" style="gap: 10px;">
                    <div style="min-width: 0;">
                        <div class="ims-kpi-label">Sales Returns</div>
                        <div class="ims-kpi-value text-danger">${{ number_format($kpis['total_returns'], 2) }}</div>
                        <div class="ims-kpi-sub text-muted">
                            {{ $kpis['returns_count'] }} returns · {{ $kpis['customers_count'] }} customers · {{ $kpis['suppliers_count'] }} suppliers
                        </div>
                    </div>
                    <span class="ims-kpi-icon ims-kpi-icon-danger"><i class="fa fa-undo"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-head flex-wrap" style="gap: 8px;">
                    <div>
                        <div class="ibox-title">Sales vs Purchases (Last 7 Days)</div>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 16px;">
                        <span class="small text-muted">7d Sales: <strong class="text-primary">${{ number_format($chartSeries['seven_day_sales'], 2) }}</strong></span>
                        <span class="small text-muted">7d Purchases: <strong class="text-success">${{ number_format($chartSeries['seven_day_purchases'], 2) }}</strong></span>
                    </div>
                </div>
                <div class="ibox-body">
                    <div style="height: 280px; position: relative;">
                        <canvas id="sales_purchases_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Payment Breakdown</div>
                    <span class="badge badge-soft-secondary">{{ $kpis['completed_orders_count'] }} sales</span>
                </div>
                <div class="ibox-body">
                    @if(count($paymentBreakdown['rows']) > 0)
                        <div style="height: 170px; position: relative;" class="mb-3">
                            <canvas id="payment_methods_chart"></canvas>
                        </div>
                        <ul class="list-group list-group-divider list-group-full mb-0">
                            @foreach($paymentBreakdown['rows'] as $row)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="ims-color-dot mr-2" style="background-color: {{ $row['color'] }};"></span>
                                        {{ $row['label'] }}
                                        <small class="text-muted">({{ $row['count'] }})</small>
                                    </span>
                                    <span class="font-weight-semibold">${{ number_format($row['total'], 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-pie-chart fa-2x mb-2 d-block"></i>
                            No completed orders yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Recent Orders</div>
                    @can('read order')
                        <a href="{{ route('orders.index') }}" class="btn btn-xs btn-light border">View All</a>
                    @endcan
                </div>
                <div class="ibox-body">
                    @if($recentOrders->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                <tr>
                                    <th class="text-nowrap">Order #</th>
                                    <th>Customer</th>
                                    <th class="text-nowrap">Total</th>
                                    <th>Status</th>
                                    <th class="text-nowrap">Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="font-weight-semibold text-nowrap">
                                            @can('read order')
                                                <a href="{{ route('orders.show', $order->id) }}">{{ $order->order_no }}</a>
                                            @else
                                                {{ $order->order_no }}
                                            @endcan
                                        </td>
                                        <td>{{ $order->customer ? $order->customer->name : 'Walk-in / Guest' }}</td>
                                        <td class="font-weight-medium text-nowrap">${{ number_format((float) $order->total, 2) }}</td>
                                        <td>
                                            @if($order->status === \App\Models\Order::STATUS_CANCELLED)
                                                <span class="badge badge-soft-danger">Cancelled</span>
                                            @else
                                                <span class="badge badge-soft-success">Completed</span>
                                            @endif
                                        </td>
                                        <td class="text-muted text-nowrap">{{ $order->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">No orders have been recorded yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Low Stock Alerts (&le; 5)</div>
                    @can('create purchase')
                        <a href="{{ route('purchases.create') }}" class="btn btn-xs btn-outline-primary">Restock</a>
                    @endcan
                </div>
                <div class="ibox-body">
                    @if($lowStockProducts->isNotEmpty())
                        <ul class="list-group list-group-divider list-group-full mb-0">
                            @foreach($lowStockProducts as $product)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center" style="min-width: 0;">
                                        <img src="{{ asset('storage/' . $product->product_image) }}" alt="{{ $product->product_name }}" class="img-50 mr-2 flex-shrink-0">
                                        <div style="min-width: 0;">
                                            <div class="font-weight-medium text-truncate">{{ $product->product_name }}</div>
                                            <small class="text-muted">{{ $product->product_code }} · {{ $product->category ? $product->category->category_name : '-' }}</small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $product->quantity <= 0 ? 'badge-soft-danger' : 'badge-soft-warning' }} ml-2 flex-shrink-0">
                                        {{ $product->quantity <= 0 ? 'Out of Stock' : $product->quantity . ' left' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-success py-3">
                            <i class="fa fa-check-circle mr-1"></i> All active products are well stocked.
                        </div>
                    @endif
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-head">
                    <div class="ibox-title">Top Selling Products</div>
                    @can('read product')
                        <a href="{{ route('products.index') }}" class="btn btn-xs btn-light border">All Products</a>
                    @endcan
                </div>
                <div class="ibox-body">
                    @if($topSellingProducts->isNotEmpty())
                        <ul class="list-group list-group-divider list-group-full mb-0">
                            @foreach($topSellingProducts as $top)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center" style="min-width: 0;">
                                        @if($top['image'])
                                            <img src="{{ $top['image'] }}" alt="{{ $top['name'] }}" class="img-50 mr-2 flex-shrink-0">
                                        @endif
                                        <div style="min-width: 0;">
                                            <div class="font-weight-medium text-truncate">{{ $top['name'] }}</div>
                                            <small class="text-muted">{{ $top['code'] }} · {{ $top['units_sold'] }} sold</small>
                                        </div>
                                    </div>
                                    <span class="font-weight-bold text-primary ml-2 flex-shrink-0">${{ number_format($top['total_revenue'], 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-3">No product sales recorded yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/Chart.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        window.DASHBOARD_DATA = {
            chartSeries: @json($chartSeries),
            paymentBreakdown: @json($paymentBreakdown),
        };
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}" type="text/javascript"></script>
@endsection
