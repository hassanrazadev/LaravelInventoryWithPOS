<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receipt {{ $order->order_no }} | {{ config('pos.store_name', 'IMS-POS') }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
    <style>
        @page {
            size: {{ $paperWidth }}mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background: #e2e8f0;
            font-family: 'Courier New', Courier, monospace;
            color: #000000;
            padding: 20px 12px;
            margin: 0;
        }
        .receipt-toolbar {
            max-width: 460px;
            margin: 0 auto 14px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }
        .receipt-toolbar-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .receipt-status {
            margin-top: 8px;
            font-size: 12.5px;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .receipt-sheet {
            width: {{ $paperWidth }}mm;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: {{ $paperWidth === 58 ? '4mm 3mm' : '5mm 4mm' }};
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
            font-size: {{ $paperWidth === 58 ? '11px' : '12px' }};
            line-height: 1.35;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #334155;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .receipt-brand {
            font-size: {{ $paperWidth === 58 ? '15px' : '18px' }};
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .receipt-sub {
            font-size: {{ $paperWidth === 58 ? '10px' : '11px' }};
            color: #334155;
        }
        .receipt-meta {
            margin-bottom: 8px;
            font-size: {{ $paperWidth === 58 ? '10.5px' : '11.5px' }};
        }
        .receipt-meta-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .receipt-divider {
            border-top: 1px dashed #334155;
            margin: 8px 0;
        }
        .receipt-items-header,
        .receipt-item-row {
            display: flex;
            justify-content: space-between;
            gap: 6px;
        }
        .receipt-items-header {
            font-weight: 700;
            border-top: 1px dashed #334155;
            border-bottom: 1px dashed #334155;
            padding: 4px 0;
            margin-bottom: 6px;
        }
        .receipt-item {
            margin-bottom: 5px;
        }
        .receipt-item-name {
            font-weight: 700;
            word-break: break-word;
        }
        .receipt-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .receipt-grand-total {
            font-size: {{ $paperWidth === 58 ? '13.5px' : '15px' }};
            font-weight: 700;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 5px 0;
            margin: 6px 0;
        }
        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #334155;
            margin-top: 10px;
            padding-top: 8px;
            font-size: {{ $paperWidth === 58 ? '10px' : '11px' }};
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .receipt-toolbar {
                display: none !important;
            }
            .receipt-sheet {
                width: {{ $paperWidth }}mm !important;
                box-shadow: none !important;
                border: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-toolbar">
        <div class="receipt-toolbar-row">
            <div class="d-flex align-items-center" style="gap: 6px;">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-light border">
                    <i class="fa fa-arrow-left mr-1"></i> Order
                </a>
                <div class="btn-group btn-group-sm" role="group" aria-label="Thermal paper width">
                    <a href="{{ route('orders.receipt', ['order' => $order->id, 'width' => 80]) }}" class="btn {{ $paperWidth === 80 ? 'btn-dark' : 'btn-light border' }}">80mm</a>
                    <a href="{{ route('orders.receipt', ['order' => $order->id, 'width' => 58]) }}" class="btn {{ $paperWidth === 58 ? 'btn-dark' : 'btn-light border' }}">58mm</a>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: 6px;">
                <button type="button" class="btn btn-sm btn-success" id="receipt-thermal-print-btn">
                    <i class="fa fa-bolt mr-1"></i> Thermal Print
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print();">
                    <i class="fa fa-print mr-1"></i> Browser Print
                </button>
            </div>
        </div>
        <div class="receipt-status d-none" id="receipt-print-status"></div>
    </div>

    <div class="receipt-sheet">
        <div class="receipt-header">
            <div class="receipt-brand">{{ config('pos.store_name', 'IMS-POS') }}</div>
            @if(config('pos.store_address'))
                <div class="receipt-sub">{{ config('pos.store_address') }}</div>
            @endif
            @if(config('pos.store_phone'))
                <div class="receipt-sub">{{ config('pos.store_phone') }}</div>
            @endif
            <div class="receipt-sub mt-1">Sales Receipt ({{ $paperWidth }}mm)</div>
        </div>

        <div class="receipt-meta">
            <div class="receipt-meta-row">
                <span>Order:</span>
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
                <span>{{ strtoupper($order->status) }}</span>
            </div>
        </div>

        <div class="receipt-items-header">
            <span>ITEM</span>
            <span>TOTAL</span>
        </div>

        @foreach($order->orderDetails as $detail)
            <div class="receipt-item">
                <div class="receipt-item-name">{{ $detail->product ? $detail->product->product_name : 'Product' }}</div>
                <div class="receipt-item-row">
                    <span>{{ $detail->quantity }} x ${{ number_format((float) $detail->unit_price, 2) }}</span>
                    <strong>${{ number_format((float) $detail->sub_total, 2) }}</strong>
                </div>
            </div>
        @endforeach

        <div class="receipt-divider"></div>

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
            <span>TOTAL</span>
            <span>${{ number_format((float) $order->total, 2) }}</span>
        </div>
        <div class="receipt-total-row">
            <span>Paid ({{ $order->paymentMethodLabel() }})</span>
            <span>${{ number_format((float) $order->paid_amount, 2) }}</span>
        </div>
        <div class="receipt-total-row">
            <span>Change</span>
            <span>${{ number_format((float) $order->change_amount, 2) }}</span>
        </div>

        @if($order->notes)
            <div class="receipt-divider"></div>
            <div>Note: {{ $order->notes }}</div>
        @endif

        <div class="receipt-footer">
            <div>{{ config('pos.receipt_footer', 'Thank you for your business!') }}</div>
            <div class="mt-1">*** {{ $order->order_no }} ***</div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        (function ($) {
            const printUrl = @json(route('orders.printThermal', $order->id));
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const defaultPrinter = @json($printerConfig);
            const paperWidth = @json($paperWidth);
            const $statusBox = $('#receipt-print-status');
            const $thermalBtn = $('#receipt-thermal-print-btn');

            function loadPrinterSettings() {
                let saved = {};
                try {
                    saved = JSON.parse(localStorage.getItem('ims_pos_printer_settings') || '{}');
                } catch (e) {
                    saved = {};
                }
                const driver = saved.driver === 'http' ? 'http' : (defaultPrinter.driver === 'http' ? 'http' : 'tcp');

                return {
                    driver: driver,
                    host: saved.host || defaultPrinter.host || '127.0.0.1',
                    port: saved.port || defaultPrinter.port || 8000,
                    http_path: saved.http_path || defaultPrinter.http_path || '/',
                    http_format: saved.http_format || defaultPrinter.http_format || 'escpos',
                    paper_width: paperWidth
                };
            }

            $thermalBtn.on('click', function () {
                const original = $thermalBtn.html();
                $thermalBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Sending...');
                $statusBox.addClass('d-none').removeClass('alert-success alert-danger').text('');

                $.ajax({
                    url: printUrl,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify(loadPrinterSettings()),
                    success: function (res) {
                        $statusBox
                            .removeClass('d-none alert-success alert-danger')
                            .addClass(res.status ? 'alert-success' : 'alert-danger')
                            .text(res.message || (res.status ? 'Sent to thermal printer.' : 'Print failed.'));
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Unable to send thermal print job.';
                        $statusBox.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
                    },
                    complete: function () {
                        $thermalBtn.prop('disabled', false).html(original);
                    }
                });
            });

            if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
                window.setTimeout(function () {
                    window.print();
                }, 250);
            }
        })(jQuery);
    </script>
</body>
</html>
