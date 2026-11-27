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
        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <button type="button" class="btn btn-success {{ config('pos.printer.driver', 'browser') === 'browser' ? 'd-none' : '' }}" id="order-thermal-print-btn" data-url="{{ route('orders.printThermal', $order->id) }}">
                <i class="fa fa-bolt mr-1"></i> Thermal Print
            </button>
            <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="btn btn-primary">
                <i class="fa fa-print mr-1"></i> Print Receipt
            </a>
            @if($order->status === \App\Models\Order::STATUS_COMPLETED)
                @can('create return')
                    <a href="{{ route('returns.create', ['order_id' => $order->id]) }}" class="btn btn-light border">
                        <i class="fa fa-undo mr-1"></i> Record Return
                    </a>
                @endcan
            @endif
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

                @if($order->returns->isNotEmpty())
                    <h6 class="mt-4 mb-3"><i class="fa fa-undo mr-2 text-danger"></i>Recorded Returns</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Product</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Refund</th>
                                <th>Restocked</th>
                                <th>Reason</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->returns as $ret)
                                <tr>
                                    <td><a href="{{ route('returns.show', $ret->id) }}">#{{ $ret->id }}</a></td>
                                    <td>{{ $ret->product ? $ret->product->product_name : '-' }}</td>
                                    <td class="text-right">{{ $ret->quantity }}</td>
                                    <td class="text-right font-weight-semibold text-danger">${{ number_format((float) $ret->return_price, 2) }}</td>
                                    <td>
                                        @if($ret->restock)
                                            <span class="badge badge-soft-success">Yes</span>
                                        @else
                                            <span class="badge badge-soft-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $ret->reason ?: '-' }}</td>
                                    <td>{{ $ret->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="text/javascript">
        (function ($) {
            const defaultPrinter = @json(config('pos.printer'));
            let savedSettings = {};
            try {
                savedSettings = JSON.parse(localStorage.getItem('ims_pos_printer_settings_v3') || '{}');
            } catch (e) {
                savedSettings = {};
            }
            const activeDriver = savedSettings.driver || defaultPrinter.driver || 'browser';
            const $thermalBtn = $('#order-thermal-print-btn');
            if (activeDriver === 'tcp' || activeDriver === 'http') {
                $thermalBtn.removeClass('d-none');
            }

            $thermalBtn.on('click', function () {
                const $btn = $(this);
                const url = $btn.attr('data-url');
                const saved = savedSettings;
                const payload = {
                    driver: saved.driver === 'http' ? 'http' : (defaultPrinter.driver === 'http' ? 'http' : 'tcp'),
                    host: saved.host || defaultPrinter.host || '127.0.0.1',
                    port: saved.port || defaultPrinter.port || 9100,
                    http_path: saved.http_path || defaultPrinter.http_path || '/',
                    http_format: saved.http_format || defaultPrinter.http_format || 'escpos',
                    paper_width: Number(saved.paper_width || defaultPrinter.paper_width || 80),
                    open_cash_drawer: false
                };

                const orig = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Printing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { Accept: 'application/json' },
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function (res) {
                        showToast(res.status ? 'Thermal Printer' : 'Printer Error', res.message, res.status ? 'success' : 'error');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to send thermal print job.';
                        showToast('Printer Error', msg, 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(orig);
                    }
                });
            });
        })(jQuery);
    </script>
@endsection
