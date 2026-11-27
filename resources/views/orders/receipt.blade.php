<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $order->order_no }} | {{ config('app.name', 'IMS-POS') }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            padding: 24px 12px;
        }
        .receipt-toolbar {
            max-width: 440px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .receipt-sheet {
            max-width: 440px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 26px 24px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        .receipt-brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .receipt-meta {
            font-size: 12.5px;
            color: #475569;
            margin-bottom: 14px;
        }
        .receipt-meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .receipt-table th,
        .receipt-table td {
            font-size: 12.5px;
            padding: 6px 4px;
            border-top: 1px solid #f1f5f9;
        }
        .receipt-totals {
            border-top: 1px dashed #cbd5e1;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 13px;
        }
        .receipt-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .receipt-grand-total {
            font-size: 16px;
            font-weight: 700;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 8px;
        }
        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            margin-top: 16px;
            padding-top: 12px;
            font-size: 12px;
            color: #64748b;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .receipt-toolbar {
                display: none !important;
            }
            .receipt-sheet {
                box-shadow: none;
                border: 0;
                max-width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-toolbar">
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-light border">
            <i class="fa fa-arrow-left mr-1"></i> Back to Order
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print();">
            <i class="fa fa-print mr-1"></i> Print Receipt
        </button>
    </div>

    <div class="receipt-sheet">
        <div class="receipt-header">
            <div class="receipt-brand">{{ config('app.name', 'IMS-POS') }}</div>
            <div class="text-muted small">Sales Receipt</div>
        </div>

        <div class="receipt-meta">
            <div class="receipt-meta-row">
                <span>Order No:</span>
                <strong>{{ $order->order_no }}</strong>
            </div>
            <div class="receipt-meta-row">
                <span>Date:</span>
                <span>{{ $order->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="receipt-meta-row">
                <span>Customer:</span>
                <span>{{ $order->customer ? $order->customer->name : 'Walk-in / Guest' }}</span>
            </div>
            <div class="receipt-meta-row">
                <span>Cashier:</span>
                <span>{{ $order->createdBy ? $order->createdBy->name : '-' }}</span>
            </div>
            <div class="receipt-meta-row">
                <span>Status:</span>
                <span class="text-uppercase font-weight-bold">{{ $order->status }}</span>
            </div>
        </div>

        <table class="table table-borderless receipt-table mb-0">
            <thead>
            <tr class="text-muted">
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td>{{ $detail->product ? $detail->product->product_name : 'Product' }}</td>
                    <td class="text-right">{{ $detail->quantity }}</td>
                    <td class="text-right">${{ number_format((float) $detail->unit_price, 2) }}</td>
                    <td class="text-right font-weight-medium">${{ number_format((float) $detail->sub_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="receipt-totals">
            <div class="receipt-total-row">
                <span>Subtotal ({{ $order->total_items }} items)</span>
                <span>${{ number_format((float) $order->sub_total, 2) }}</span>
            </div>
            <div class="receipt-total-row">
                <span>Discount</span>
                <span>-${{ number_format((float) $order->discount, 2) }}</span>
            </div>
            <div class="receipt-total-row">
                <span>Tax</span>
                <span>${{ number_format((float) $order->tax, 2) }}</span>
            </div>
            <div class="receipt-total-row receipt-grand-total">
                <span>Total</span>
                <span>${{ number_format((float) $order->total, 2) }}</span>
            </div>
            <div class="receipt-total-row mt-2">
                <span>Paid ({{ $order->paymentMethodLabel() }})</span>
                <span>${{ number_format((float) $order->paid_amount, 2) }}</span>
            </div>
            <div class="receipt-total-row">
                <span>Change</span>
                <span>${{ number_format((float) $order->change_amount, 2) }}</span>
            </div>
        </div>

        @if($order->notes)
            <div class="small text-muted mt-2">Note: {{ $order->notes }}</div>
        @endif

        <div class="receipt-footer">
            Thank you for your business!
        </div>
    </div>
</body>
</html>
