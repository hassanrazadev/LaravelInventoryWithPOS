@extends('layouts.master')

@section('title', 'Create Order / POS')

@section('body-classes', ' sidebar-mini')

@section('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Point of Sale</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                <li class="breadcrumb-item">POS Terminal</li>
            </ol>
        </div>
        @can('read order')
            <a href="{{ route('orders.index') }}" class="btn btn-light border">
                <i class="fa fa-list mr-1"></i> Order History
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="row">
            <!-- Left: Product Catalog -->
            <div class="col-lg-7 col-12">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title">Product Catalog</div>
                        <span class="badge badge-soft-secondary" id="pos-catalog-count">{{ $products->count() }} products</span>
                    </div>
                    <div class="ibox-body">
                        <div class="pos-search-bar mb-3 d-flex align-items-center" style="gap: 8px;">
                            <div class="input-group-icon flex-grow-1">
                                <div class="input-icon"><i class="fa fa-search text-muted"></i></div>
                                <input type="text" class="form-control" id="pos-product-search" placeholder="Search by product name or scan SKU code (press Enter to add)..." autocomplete="off">
                            </div>
                            <button class="btn btn-light border d-none flex-shrink-0" id="pos-search-clear" type="button" title="Clear search"><i class="fa fa-times"></i></button>
                        </div>

                        <div class="pos-category-pills mb-3" id="pos-category-pills">
                            <button type="button" class="btn btn-sm pos-category-pill active" data-category-id="">All Products</button>
                            @foreach($categories as $category)
                                <button type="button" class="btn btn-sm pos-category-pill" data-category-id="{{ $category->id }}">{{ $category->category_name }}</button>
                            @endforeach
                        </div>

                        <div class="row" id="pos-product-grid"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Cart & Checkout -->
            <div class="col-lg-5 col-12">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title d-flex align-items-center">
                            <span>Current Cart</span>
                            <span class="badge badge-soft-primary ml-2" id="pos-cart-items-count">0 items</span>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-danger" id="pos-clear-cart" disabled>
                            <i class="fa fa-trash mr-1"></i> Clear
                        </button>
                    </div>
                    <div class="ibox-body">
                        <form id="pos-checkout-form">
                            <div class="form-group">
                                <label for="customer_id" class="font-weight-medium mb-1">Customer</label>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 mr-2" style="min-width: 0;">
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
                                        <button type="button" class="btn btn-outline-primary flex-shrink-0" data-toggle="modal" data-target="#pos-add-customer-modal" title="Quick add customer">
                                            <i class="fa fa-plus mr-1"></i> New
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive pos-cart-table-wrap mb-3">
                                <table class="table table-sm mb-0" id="pos-cart-table">
                                    <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="pos-cart-body"></tbody>
                                </table>
                            </div>

                            <div class="pos-summary-box mb-3">
                                <div class="pos-summary-row">
                                    <span>Subtotal</span>
                                    <span class="font-weight-semibold" id="pos-subtotal">$0.00</span>
                                </div>
                                <div class="pos-summary-row align-items-center">
                                    <label for="pos-discount" class="mb-0">Discount ($)</label>
                                    <input type="number" step="0.01" min="0" value="0" id="pos-discount" class="form-control form-control-sm pos-inline-input">
                                </div>
                                <div class="pos-summary-row align-items-center">
                                    <label for="pos-tax-rate" class="mb-0">Tax (%)</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" step="0.01" min="0" max="100" value="0" id="pos-tax-rate" class="form-control form-control-sm pos-inline-input mr-2">
                                        <span class="text-muted small" id="pos-tax-amount">$0.00</span>
                                    </div>
                                </div>
                                <div class="pos-summary-total">
                                    <span>Grand Total</span>
                                    <span id="pos-grand-total">$0.00</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="pos-payment-method">Payment Method</label>
                                        <select id="pos-payment-method" class="form-control">
                                            @foreach($paymentMethods as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="pos-paid-amount">Amount Paid</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" id="pos-paid-amount" class="form-control" placeholder="0.00">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-light border" id="pos-exact-btn">Exact</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 px-2 py-2 rounded bg-light">
                                <div>
                                    <div class="small text-muted">Change Due</div>
                                    <div class="font-weight-bold text-success font-16" id="pos-change-due">$0.00</div>
                                </div>
                                <div class="small text-right" id="pos-payment-hint"></div>
                            </div>

                            <div class="form-group">
                                <input type="text" id="pos-notes" maxlength="500" class="form-control" placeholder="Optional order note (e.g. counter #2, warranty ref)...">
                            </div>

                            <div class="alert alert-danger d-none mb-3" id="pos-checkout-error"></div>

                            <button type="submit" class="btn btn-primary btn-block py-2 font-weight-semibold" id="pos-checkout-btn" disabled>
                                <i class="fa fa-check-circle mr-1"></i> Complete Sale &amp; Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

    <div class="modal fade" id="pos-checkout-success-modal" tabindex="-1" role="dialog" aria-labelledby="posCheckoutSuccessLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="pos-success-icon mx-auto mb-3">
                        <i class="fa fa-check"></i>
                    </div>
                    <h4 class="font-weight-bold mb-1" id="posCheckoutSuccessLabel">Sale Completed!</h4>
                    <p class="text-muted mb-3">Invoice <strong id="pos-success-order-no"></strong> has been recorded.</p>

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
                        <a href="#" target="_blank" class="btn btn-primary" id="pos-success-receipt-btn">
                            <i class="fa fa-print mr-1"></i> Print Receipt
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
        };
    </script>
    <script src="{{ asset('assets/js/pos.js') }}" type="text/javascript"></script>
@endsection
