@extends('layouts.master')

@section('title', 'Manage Orders')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Orders</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Orders</li>
            </ol>
        </div>
        @can('create order')
            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="fa fa-shopping-basket mr-1"></i> New Sale / POS
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Total Orders</div>
                    <div class="ims-kpi-value">{{ $stats['total_orders'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Completed Revenue</div>
                    <div class="ims-kpi-value text-primary">${{ number_format($stats['completed_revenue'], 2) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Today's Sales</div>
                    <div class="ims-kpi-value text-success">${{ number_format($stats['today_revenue'], 2) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Cancelled Orders</div>
                    <div class="ims-kpi-value text-danger">{{ $stats['cancelled_orders'] }}</div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Order History</div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <a href="{{ route('orders.index') }}" class="btn btn-xs {{ $statusFilter === null ? 'btn-primary' : 'btn-light border' }}">All</a>
                    <a href="{{ route('orders.index', ['status' => \App\Models\Order::STATUS_COMPLETED]) }}" class="btn btn-xs {{ $statusFilter === \App\Models\Order::STATUS_COMPLETED ? 'btn-primary' : 'btn-light border' }}">Completed</a>
                    <a href="{{ route('orders.index', ['status' => \App\Models\Order::STATUS_CANCELLED]) }}" class="btn btn-xs {{ $statusFilter === \App\Models\Order::STATUS_CANCELLED ? 'btn-primary' : 'btn-light border' }}">Cancelled</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="orders-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Cashier</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr data-index="{{ $order->id }}">
                            <td class="font-weight-semibold">{{ $order->order_no }}</td>
                            <td>{{ $order->customer ? $order->customer->name : 'Walk-in / Guest' }}</td>
                            <td>{{ $order->total_items }}</td>
                            <td class="font-weight-medium">${{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ $order->paymentMethodLabel() }}</td>
                            <td class="order-status-cell">
                                @if($order->status === \App\Models\Order::STATUS_CANCELLED)
                                    <span class="badge badge-soft-danger">Cancelled</span>
                                @else
                                    <span class="badge badge-soft-success">Completed</span>
                                @endif
                            </td>
                            <td>{{ $order->createdBy ? $order->createdBy->name : '-' }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-xs m-r-5" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="btn btn-light border btn-xs m-r-5" data-toggle="tooltip" data-original-title="Print Receipt"><i class="fa fa-print font-14"></i></a>
                                @canany(['update order', 'delete order'])
                                    @if($order->status === \App\Models\Order::STATUS_COMPLETED)
                                        <a href="{{ route('orders.cancel', $order->id) }}" class="btn btn-warning cancel-order btn-xs m-r-5" data-toggle="tooltip" data-original-title="Cancel &amp; Restore Stock"><i class="fa fa-ban font-14"></i></a>
                                    @endif
                                @endcanany
                                @can('delete order')
                                    <a href="{{ route('orders.destroy', $order->id) }}" class="btn btn-danger delete-order btn-xs" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        let dataTable = $('#orders-table').DataTable({
            pageLength: 10,
            order: [[7, 'desc']]
        });

        $(document).on('click', '.cancel-order', function (e) {
            e.preventDefault();
            if (!confirm('Cancel this order and restore product stock quantities?')) {
                return;
            }
            let btn = $(this);
            let row = btn.closest('tr');
            $.ajax({
                url: btn.attr('href'),
                type: 'PATCH',
                headers: { Accept: 'application/json' },
                success: function (response) {
                    let data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.status) {
                        showToast('Success', data.message, 'success');
                        row.find('.order-status-cell').html('<span class="badge badge-soft-danger">Cancelled</span>');
                        btn.tooltip('dispose').remove();
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                }
            });
        });

        $(document).on('click', '.delete-order', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this order?')) {
                let btn = $(this);
                let row = btn.parents('tr');
                $.ajax({
                    url: btn.attr('href'),
                    type: 'DELETE',
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status) {
                            showToast('Success', data.message, 'success');
                            btn.tooltip('dispose');
                            dataTable.row(row).remove().draw(false);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    }
                });
            }
        });
    </script>
@endsection
