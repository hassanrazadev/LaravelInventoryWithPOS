@extends('layouts.master')

@section('title', $customer->name)

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Customer Details</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item">{{ $customer->name }}</li>
            </ol>
        </div>
        @can('update customer')
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                <i class="fa fa-pencil mr-1"></i> Edit Customer
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex align-items-center">
                    <span class="ims-avatar ims-avatar-sm mr-2">{{ \App\Utils\AppUtils::initials($customer->name) }}</span>
                    <span>{{ $customer->name }}</span>
                </div>
                <div>
                    @if($customer->trashed())
                        <span class="badge badge-soft-danger">Inactive</span>
                    @else
                        <span class="badge badge-soft-success">Active</span>
                    @endif
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Phone</div>
                        <div>{{ $customer->phone }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Email</div>
                        <div>{{ $customer->email ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Total Completed Spend</div>
                        <div class="font-weight-bold text-primary">${{ number_format($totalSpent, 2) }} ({{ $customer->orders->count() }} orders)</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Created By</div>
                        <div>{{ $customer->createdBy ? $customer->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Updated By</div>
                        <div>{{ $customer->updatedBy ? $customer->updatedBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Address</div>
                        <div>{{ $customer->address ?: '-' }}</div>
                    </div>
                </div>

                <h6 class="m-b-20 m-t-20"><i class="fa fa-shopping-basket mr-2"></i>Order History</h6>
                <table class="table table-striped table-bordered table-hover" id="customer-orders-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Cashier</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customer->orders as $order)
                        <tr>
                            <td class="font-weight-medium">{{ $order->order_no }}</td>
                            <td>{{ $order->total_items }}</td>
                            <td class="font-weight-medium">${{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ $order->paymentMethodLabel() }}</td>
                            <td>
                                @if($order->status === \App\Models\Order::STATUS_CANCELLED)
                                    <span class="badge badge-soft-danger">Cancelled</span>
                                @else
                                    <span class="badge badge-soft-success">Completed</span>
                                @endif
                            </td>
                            <td>{{ $order->createdBy ? $order->createdBy->name : '-' }}</td>
                            <td>{{ $order->created_at }}</td>
                            <td class="text-center">
                                @can('read order')
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-xs" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $('#customer-orders-table').DataTable({
            pageLength: 10
        });
    </script>
@endsection
