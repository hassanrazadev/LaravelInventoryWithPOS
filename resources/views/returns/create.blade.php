@extends('layouts.master')

@section('title', 'Record Sales Return')

@section('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Record Sales Return</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
                <li class="breadcrumb-item">Create</li>
            </ol>
        </div>
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Return Line Item</div>
            </div>
            <div class="ibox-body">
                <form action="{{ route('returns.store') }}" method="POST" id="return-create-form">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="order_id">Completed Order</label>
                                @php
                                    $currentOrderId = old('order_id', $selectedOrderId);
                                @endphp
                                <select id="order_id" name="order_id" class="form-control select2_order {{ \App\Utils\AppUtils::inputFieldError($errors, 'order_id') }}" required>
                                    <option value="">Select completed order</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order->id }}" {{ (string) $currentOrderId === (string) $order->id ? 'selected' : '' }}>
                                            {{ $order->order_no }} — {{ $order->customer ? $order->customer->name : 'Walk-in / Guest' }} (${{ number_format((float) $order->total, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @include('partials._error', ['field' => 'order_id'])
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="product_id">Order Product</label>
                                <select id="product_id" name="product_id" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'product_id') }}" required disabled>
                                    <option value="">Select an order first</option>
                                </select>
                                <small class="form-text text-muted" id="return-item-hint">Choose a completed order to load its returnable line items.</small>
                                @include('partials._error', ['field' => 'product_id'])
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group required">
                                <label for="quantity">Return Quantity</label>
                                <input type="number" min="1" id="quantity" name="quantity" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'quantity') }}" value="{{ old('quantity', 1) }}" required>
                                @include('partials._error', ['field' => 'quantity'])
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="return_price">Refund Amount ($)</label>
                                <input type="number" step="0.01" min="0" id="return_price" name="return_price" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'return_price') }}" value="{{ old('return_price') }}" placeholder="Auto-calculated if blank">
                                @include('partials._error', ['field' => 'return_price'])
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-group mt-3 mb-0">
                                <input type="hidden" name="restock" value="0">
                                @php
                                    $shouldRestock = old('restock', '1');
                                @endphp
                                <label class="ui-checkbox ui-checkbox-primary mb-0">
                                    <input type="checkbox" name="restock" value="1" {{ (int) $shouldRestock === 1 ? 'checked' : '' }}>
                                    <span class="input-span"></span>Restock returned units into inventory
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="reason">Return Reason / Notes</label>
                                <textarea id="reason" name="reason" rows="2" maxlength="500" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'reason') }}" placeholder="e.g. Defective item, customer size exchange, unopened return">{{ old('reason') }}</textarea>
                                @include('partials._error', ['field' => 'reason'])
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-end">
                        <a href="{{ route('returns.index') }}" class="btn btn-light mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Record Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/select2.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        (function ($) {
            const $orderSelect = $('#order_id');
            const $productSelect = $('#product_id');
            const $qtyInput = $('#quantity');
            const $refundInput = $('#return_price');
            const $hint = $('#return-item-hint');
            const oldProductId = @json(old('product_id'));
            const orderItemsUrlTemplate = @json(route('returns.orderItems', ['order' => '__ORDER__']));
            let refundManuallyEdited = $refundInput.val() !== '';

            $orderSelect.select2({
                placeholder: 'Select completed order',
                width: '100%'
            });

            function updateRefundEstimate() {
                const $selected = $productSelect.find('option:selected');
                const unitPrice = parseFloat($selected.attr('data-unit-price'));
                const remaining = parseInt($selected.attr('data-remaining'), 10);

                if (!isNaN(remaining) && remaining > 0) {
                    $qtyInput.attr('max', remaining);
                    if (parseInt($qtyInput.val(), 10) > remaining) {
                        $qtyInput.val(remaining);
                    }
                    $hint.text('Remaining returnable: ' + remaining + ' unit(s) @ $' + unitPrice.toFixed(2) + ' each.');
                }

                if (!refundManuallyEdited && !isNaN(unitPrice)) {
                    const qty = Math.max(1, parseInt($qtyInput.val(), 10) || 1);
                    $refundInput.val((unitPrice * qty).toFixed(2));
                }
            }

            function loadOrderItems(orderId, preselectProductId) {
                $productSelect.empty().prop('disabled', true);

                if (!orderId) {
                    $productSelect.append(new Option('Select an order first', ''));
                    $hint.text('Choose a completed order to load its returnable line items.');
                    return;
                }

                $productSelect.append(new Option('Loading order items...', ''));

                $.getJSON(orderItemsUrlTemplate.replace('__ORDER__', orderId))
                    .done(function (response) {
                        $productSelect.empty();
                        if (!response || !response.status || !Array.isArray(response.items) || response.items.length === 0) {
                            $productSelect.append(new Option('No items available', ''));
                            $hint.text('This order has no returnable line items.');
                            return;
                        }

                        $productSelect.append(new Option('Select product to return', ''));
                        let returnableCount = 0;

                        response.items.forEach(function (item) {
                            const label = item.product_name + ' (' + item.product_code + ') — Remaining: ' + item.remaining_qty + ' of ' + item.purchased_qty;
                            const opt = new Option(label, item.product_id);
                            $(opt).attr('data-remaining', item.remaining_qty);
                            $(opt).attr('data-unit-price', item.unit_price);
                            if (item.remaining_qty <= 0) {
                                $(opt).prop('disabled', true);
                            } else {
                                returnableCount++;
                            }
                            if (preselectProductId && String(preselectProductId) === String(item.product_id)) {
                                $(opt).prop('selected', true);
                            }
                            $productSelect.append(opt);
                        });

                        $productSelect.prop('disabled', returnableCount === 0);
                        $hint.text(returnableCount > 0 ? 'Select an item to calculate suggested refund.' : 'All items from this order have already been returned.');
                        updateRefundEstimate();
                    })
                    .fail(function () {
                        $productSelect.empty().append(new Option('Failed to load order items', ''));
                        $hint.text('Unable to fetch line items for the selected order.');
                    });
            }

            $orderSelect.on('change', function () {
                refundManuallyEdited = false;
                $refundInput.val('');
                loadOrderItems($(this).val(), null);
            });

            $productSelect.on('change', function () {
                refundManuallyEdited = false;
                updateRefundEstimate();
            });

            $qtyInput.on('input change', function () {
                updateRefundEstimate();
            });

            $refundInput.on('input', function () {
                refundManuallyEdited = $(this).val() !== '';
            });

            if ($orderSelect.val()) {
                loadOrderItems($orderSelect.val(), oldProductId);
            }
        })(jQuery);
    </script>
@endsection
