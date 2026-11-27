@extends('layouts.master')

@section('title', 'Point of Sale')

@section('body-classes', ' sidebar-mini pos-page')

@section('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="pos-topbar">
        <div class="pos-topbar-left">
            <h1 class="pos-title">Point of Sale</h1>
            <span class="badge badge-soft-secondary" id="pos-catalog-count">{{ $products->count() }} products</span>
        </div>
        <div class="pos-topbar-actions">
            <button type="button" class="btn btn-light border btn-sm pos-printer-pill" id="pos-printer-settings-btn" data-toggle="modal" data-target="#pos-printer-settings-modal">
                <i class="fa fa-print text-primary mr-1"></i>
                <span id="pos-printer-summary">
                    @if(($printerConfig['driver'] ?? 'browser') === 'browser')
                        Browser Print · {{ $printerConfig['paper_width'] ?? 80 }}mm
                    @else
                        {{ $printerConfig['host'] ?? '127.0.0.1' }}:{{ $printerConfig['port'] ?? 9100 }} · {{ $printerConfig['paper_width'] ?? 80 }}mm
                    @endif
                </span>
            </button>
            @can('read order')
                <a href="{{ route('orders.index') }}" class="btn btn-light border btn-sm">
                    <i class="fa fa-list mr-1"></i> Order History
                </a>
            @endcan
        </div>
    </div>

    <div class="pos-workspace">
        <!-- Left: Search, Filters & Product Grid -->
        <section class="pos-catalog-pane">
            <div class="pos-catalog-toolbar">
                <div class="pos-search-bar d-flex align-items-center" style="gap: 8px;">
                    <div class="input-group-icon flex-grow-1">
                        <div class="input-icon"><i class="fa fa-search text-muted"></i></div>
                        <input type="text" class="form-control" id="pos-product-search" placeholder="Search product or scan SKU (Enter to add)..." autocomplete="off">
                    </div>
                    <button class="btn btn-light border d-none flex-shrink-0" id="pos-search-clear" type="button" title="Clear search">
                        <i class="fa fa-times"></i>
                    </button>
                    <button class="btn btn-light border flex-shrink-0 pos-instock-btn" id="pos-instock-toggle" type="button" aria-pressed="false" title="Filter to in-stock products only">
                        <i class="fa fa-check-circle mr-1"></i> In Stock
                    </button>
                </div>

                <div class="pos-category-pills mt-2" id="pos-category-pills">
                    <button type="button" class="btn btn-sm pos-category-pill active" data-category-id="">All ({{ $products->count() }})</button>
                    @foreach($categories as $category)
                        <button type="button" class="btn btn-sm pos-category-pill" data-category-id="{{ $category->id }}">
                            {{ $category->category_name }} ({{ $category->products_count }})
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pos-catalog-grid" id="pos-product-grid"></div>
        </section>

        <!-- Right: Sticky Cart & Checkout Rail -->
        <aside class="pos-cart-pane">
            <form id="pos-checkout-form" class="pos-cart-card">
                <div class="pos-cart-header">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <h2 class="pos-cart-title">Current Sale</h2>
                        <span class="badge badge-soft-primary" id="pos-cart-items-count">0 items</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-danger" id="pos-clear-cart" disabled>
                        <i class="fa fa-trash mr-1"></i> Clear
                    </button>
                </div>

                <div class="pos-customer-bar">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            @php
                                $walkInCustomer = $customers->first(fn ($c) => stripos($c->name, 'Walk-in') !== false);
                            @endphp
                            <select class="form-control select2_customer" name="customer_id" id="customer_id">
                                <option value="">Walk-in / Guest Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $walkInCustomer && $walkInCustomer->id === $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @can('create customer')
                            <button type="button" class="btn btn-outline-primary pos-add-customer-btn flex-shrink-0" data-toggle="modal" data-target="#pos-add-customer-modal" title="Quick add customer">
                                <i class="fa fa-user-plus"></i>
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="pos-cart-list" id="pos-cart-list"></div>

                <div class="pos-checkout-footer">
                    <div class="pos-adjust-row">
                        <div class="pos-adjust-field">
                            <label for="pos-discount">Discount ($)</label>
                            <input type="number" step="0.01" min="0" value="0" id="pos-discount" class="form-control form-control-sm">
                        </div>
                        <div class="pos-adjust-field">
                            <label for="pos-tax-rate">Tax (%)</label>
                            <input type="number" step="0.01" min="0" max="100" value="0" id="pos-tax-rate" class="form-control form-control-sm">
                        </div>
                        <div class="pos-adjust-note-col">
                            <button type="button" class="btn btn-sm btn-light border btn-block" id="pos-note-toggle">
                                <i class="fa fa-sticky-note-o mr-1"></i> Note
                            </button>
                        </div>
                    </div>

                    <div class="pos-notes-wrap d-none mt-2" id="pos-notes-wrap">
                        <input type="text" id="pos-notes" maxlength="500" class="form-control form-control-sm" placeholder="Optional order note (counter, receipt ref)...">
                    </div>

                    <input type="hidden" id="pos-payment-method" value="cash">
                    <div class="pos-pay-pills mt-2" id="pos-pay-pills" role="group" aria-label="Payment Method">
                        @php
                            $payIcons = [
                                'cash' => 'fa-money',
                                'card' => 'fa-credit-card',
                                'bank_transfer' => 'fa-university',
                                'other' => 'fa-ellipsis-h',
                            ];
                        @endphp
                        @foreach($paymentMethods as $key => $label)
                            <button type="button" class="pos-pay-pill {{ $key === 'cash' ? 'active' : '' }}" data-method="{{ $key }}">
                                <i class="fa {{ $payIcons[$key] ?? 'fa-money' }}"></i>
                                <span>{{ $key === 'bank_transfer' ? 'Bank' : $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="pos-tender-row mt-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Paid ($)</span>
                            <input type="number" step="0.01" min="0" id="pos-paid-amount" class="form-control" placeholder="0.00">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-light border" id="pos-exact-btn">Exact</button>
                            </span>
                        </div>
                        <div class="pos-cash-chips mt-1" id="pos-cash-chips">
                            <button type="button" class="pos-cash-chip" data-add="5">$5</button>
                            <button type="button" class="pos-cash-chip" data-add="10">$10</button>
                            <button type="button" class="pos-cash-chip" data-add="20">$20</button>
                            <button type="button" class="pos-cash-chip" data-add="50">$50</button>
                            <button type="button" class="pos-cash-chip" data-add="100">$100</button>
                        </div>
                    </div>

                    <div class="pos-totals-strip mt-2">
                        <div class="pos-totals-breakdown text-muted">
                            <span>Sub: <strong class="text-dark" id="pos-subtotal">$0.00</strong></span>
                            <span>Tax: <strong class="text-dark" id="pos-tax-amount">$0.00</strong></span>
                            <span id="pos-change-pill" class="text-success font-weight-semibold">Change: <strong id="pos-change-due">$0.00</strong></span>
                        </div>
                        <div class="pos-totals-grand">
                            <span>Total</span>
                            <span id="pos-grand-total">$0.00</span>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none mt-2 mb-0 py-2 px-3 small" id="pos-checkout-error"></div>

                    <div class="pos-action-bar mt-2">
                        <button type="submit" class="btn btn-primary pos-pay-btn" id="pos-checkout-btn" disabled>
                            <i class="fa fa-check-circle mr-1"></i>
                            <span id="pos-checkout-btn-label">Complete Sale</span>
                        </button>
                        <button type="button" class="btn btn-success pos-print-pay-btn" id="pos-checkout-print-btn" disabled title="Complete sale and print receipt immediately">
                            <i class="fa fa-print mr-1"></i> Pay &amp; Print
                        </button>
                    </div>
                </div>
            </form>
        </aside>
    </div>

    @can('create customer')
        <div class="modal fade" id="pos-add-customer-modal" tabindex="-1" role="dialog" aria-labelledby="posAddCustomerLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="posAddCustomerLabel">Quick Add Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="pos-quick-customer-form">
                        <div class="modal-body">
                            <div class="alert alert-danger d-none" id="pos-quick-customer-errors"></div>
                            <div class="form-group required">
                                <label for="quick_customer_name">Full Name</label>
                                <input type="text" id="quick_customer_name" name="name" maxlength="50" class="form-control" placeholder="Customer name" required>
                            </div>
                            <div class="form-group required">
                                <label for="quick_customer_phone">Phone Number</label>
                                <input type="text" id="quick_customer_phone" name="phone" maxlength="20" class="form-control" placeholder="e.g. +1-555-0199" required>
                            </div>
                            <div class="form-group">
                                <label for="quick_customer_email">Email Address</label>
                                <input type="email" id="quick_customer_email" name="email" maxlength="50" class="form-control" placeholder="Optional">
                            </div>
                            <div class="form-group mb-0">
                                <label for="quick_customer_address">Address</label>
                                <input type="text" id="quick_customer_address" name="address" maxlength="500" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="pos-quick-customer-submit">Save Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <div class="modal fade" id="pos-printer-settings-modal" tabindex="-1" role="dialog" aria-labelledby="posPrinterSettingsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="posPrinterSettingsLabel"><i class="fa fa-print text-primary mr-2"></i>Receipt Printer Settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="pos-printer-settings-form">
                    <div class="modal-body">
                        <div class="alert d-none" id="pos-printer-test-status"></div>
                        <div class="form-group">
                            <label for="printer_driver">Print Transport</label>
                            <select id="printer_driver" class="form-control">
                                <option value="browser">Browser Print Dialog (Default — 80mm / 58mm Thermal CSS)</option>
                                <option value="tcp">Raw ESC/POS TCP Socket (Optional — LAN Thermal Printer)</option>
                                <option value="http">HTTP POST Bridge (Optional — Local Print Server)</option>
                            </select>
                        </div>
                        <div id="pos-printer-network-fields">
                            <div class="row">
                                <div class="col-sm-8">
                                    <div class="form-group mb-1">
                                        <label for="printer_host">Printer Host / IP</label>
                                        <input type="text" id="printer_host" class="form-control" placeholder="192.168.1.50">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group mb-1">
                                        <label for="printer_port">Port</label>
                                        <input type="number" id="printer_port" min="1" max="65535" class="form-control" placeholder="9100">
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted mb-3">Optional: enter your LAN ESC/POS thermal printer IP and port (typically 9100). Restricted to localhost and private LAN IPs.</small>
                            <div class="row d-none" id="pos-printer-http-fields">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="printer_http_path">HTTP Path</label>
                                        <input type="text" id="printer_http_path" class="form-control" placeholder="/">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="printer_http_format">Payload Format</label>
                                        <select id="printer_http_format" class="form-control">
                                            <option value="escpos">ESC/POS Raw Bytes</option>
                                            <option value="text">Plain Text Receipt</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="printer_paper_width">Paper Width</label>
                                    <select id="printer_paper_width" class="form-control">
                                        <option value="80">80mm (48 columns)</option>
                                        <option value="58">58mm (32 columns)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 d-flex align-items-center">
                                <div class="form-group mt-3 mb-0">
                                    <label class="ui-checkbox ui-checkbox-primary mb-0">
                                        <input type="checkbox" id="printer_autoprint">
                                        <span class="input-span"></span>Auto-print on Complete Sale
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-2 mb-0" id="pos-printer-drawer-field">
                            <label class="ui-checkbox ui-checkbox-primary mb-0">
                                <input type="checkbox" id="printer_open_drawer">
                                <span class="input-span"></span>Open cash drawer on Cash sale (ESC/POS Pin 2)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-primary" id="pos-test-print-btn">
                            <i class="fa fa-bolt mr-1"></i> Send Test Print
                        </button>
                        <div>
                            <button type="button" class="btn btn-light mr-1" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pos-checkout-success-modal" tabindex="-1" role="dialog" aria-labelledby="posCheckoutSuccessLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="pos-success-icon mx-auto mb-3">
                        <i class="fa fa-check"></i>
                    </div>
                    <h4 class="font-weight-bold mb-1" id="posCheckoutSuccessLabel">Sale Completed!</h4>
                    <p class="text-muted mb-2">Invoice <strong id="pos-success-order-no"></strong> has been recorded.</p>
                    <div class="alert d-none text-left small py-2 px-3 mb-3" id="pos-success-print-status"></div>

                    <div class="pos-summary-box text-left mb-4">
                        <div class="pos-summary-row">
                            <span>Total Items</span>
                            <span class="font-weight-semibold" id="pos-success-items">0</span>
                        </div>
                        <div class="pos-summary-row">
                            <span>Grand Total</span>
                            <span class="font-weight-bold text-dark" id="pos-success-total">$0.00</span>
                        </div>
                        <div class="pos-summary-row">
                            <span>Amount Paid</span>
                            <span id="pos-success-paid">$0.00</span>
                        </div>
                        <div class="pos-summary-row mb-0">
                            <span>Change Returned</span>
                            <span class="font-weight-bold text-success" id="pos-success-change">$0.00</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center flex-wrap" style="gap: 8px;">
                        <button type="button" class="btn btn-primary" id="pos-success-thermal-btn">
                            <i class="fa fa-bolt mr-1"></i> Thermal Print
                        </button>
                        <a href="#" target="_blank" class="btn btn-light border" id="pos-success-receipt-btn">
                            <i class="fa fa-print mr-1"></i> Receipt Preview
                        </a>
                        <a href="#" class="btn btn-light border" id="pos-success-view-btn">
                            <i class="fa fa-eye mr-1"></i> View Order
                        </a>
                        <button type="button" class="btn btn-success" data-dismiss="modal">
                            <i class="fa fa-plus mr-1"></i> New Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/select2.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        window.POS_CONFIG = {
            products: @json($products),
            productsUrl: @json(route('orders.products')),
            checkoutUrl: @json(route('orders.store')),
            storeCustomerUrl: @json(route('customers.store')),
            printerTestUrl: @json(route('orders.printer.test')),
            defaultPrinter: @json($printerConfig),
        };
    </script>
    <script src="{{ asset('assets/js/pos.js') }}" type="text/javascript"></script>
@endsection
