@extends('layouts.master')

@section('title', 'Order ' . $order->order_no)

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Order {{ $order->order_no }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                <li class="breadcrumb-item">{{ $order->order_no }}</li>
            </ol>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="btn btn-primary">
                <i class="fa fa-print mr-1"></i> Print Receipt
            </a>
            @canany(['update order', 'delete order'])
                @if($order->status === \App\Models\Order::STATUS_COMPLETED)
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="m-0" onsubmit="return confirm('Cancel this order and restore stock?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-ban mr-1"></i> Cancel Order
                        </button>
                    </form>
                @endif
            @endcanany
        </div>
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Order Summary</div>
                <div>
                    @if($order->status === \App\Models\Order::STATUS_CANCELLED)
                        <span class="badge badge-soft-danger">Cancelled</span>
                    @else
                        <span class="badge badge-soft-success">Completed</span>
                    @endif
                </div>
            </div>
            <div class="ibox-body">
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Customer</div>
                        <div class="font-weight-medium">
                            @if($order->customer)
                                <a href="{{ route('customers.show', $order->customer->id) }}">{{ $order->customer->name }}</a>
                            @else
                                Walk-in / Guest
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Cashier</div>
                        <div>{{ $order->createdBy ? $order->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Payment Method</div>
                        <div>{{ $order->paymentMethodLabel() }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Created At</div>
                        <div>{{ $order->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @if($order->notes)
                        <div class="col-12">
                            <div class="text-muted small font-weight-bold text-uppercase">Notes</div>
                            <div>{{ $order->notes }}</div>
                        </div>
                    @endif
                </div>

                <h6 class="mb-3"><i class="fa fa-cubes mr-2"></i>Line Items</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->orderDetails as $idx => $detail)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="font-weight-medium">{{ $detail->product ? $detail->product->product_name : 'Deleted Product' }}</td>
                                <td>{{ $detail->product ? $detail->product->product_code : '-' }}</td>
                                <td class="text-right">${{ number_format((float) $detail->unit_price, 2) }}</td>
                                <td class="text-right">{{ $detail->quantity }}</td>
                                <td class="text-right font-weight-semibold">${{ number_format((float) $detail->sub_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Subtotal ({{ $order->total_items }} items)</th>
                            <th class="text-right">${{ number_format((float) $order->sub_total, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Discount</th>
                            <th class="text-right">-${{ number_format((float) $order->discount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Tax</th>
                            <th class="text-right">${{ number_format((float) $order->tax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right font-16">Grand Total</th>
                            <th class="text-right font-16 text-primary">${{ number_format((float) $order->total, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Paid ({{ $order->paymentMethodLabel() }})</th>
                            <th class="text-right">${{ number_format((float) $order->paid_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-right">Change Returned</th>
                            <th class="text-right text-success">${{ number_format((float) $order->change_amount, 2) }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
