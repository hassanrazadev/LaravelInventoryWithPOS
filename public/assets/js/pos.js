(function ($) {
    'use strict';

    const config = window.POS_CONFIG || {};
    let catalog = Array.isArray(config.products) ? config.products : [];
    const cart = new Map();
    let activeCategoryId = '';
    let searchTerm = '';
    let isSubmitting = false;

    const $search = $('#pos-product-search');
    const $searchClear = $('#pos-search-clear');
    const $categoryPills = $('#pos-category-pills');
    const $catalogCount = $('#pos-catalog-count');
    const $grid = $('#pos-product-grid');
    const $cartBody = $('#pos-cart-body');
    const $cartItemsCount = $('#pos-cart-items-count');
    const $clearCartBtn = $('#pos-clear-cart');
    const $discount = $('#pos-discount');
    const $taxRate = $('#pos-tax-rate');
    const $subtotalEl = $('#pos-subtotal');
    const $taxAmountEl = $('#pos-tax-amount');
    const $grandTotalEl = $('#pos-grand-total');
    const $paymentMethod = $('#pos-payment-method');
    const $paidAmount = $('#pos-paid-amount');
    const $exactBtn = $('#pos-exact-btn');
    const $changeEl = $('#pos-change-due');
    const $paymentHint = $('#pos-payment-hint');
    const $notes = $('#pos-notes');
    const $customerSelect = $('#customer_id');
    const $checkoutBtn = $('#pos-checkout-btn');
    const $errorAlert = $('#pos-checkout-error');

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function money(amount) {
        return '$' + Number(amount || 0).toFixed(2);
    }

    function round2(value) {
        return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
    }

    function filteredProducts() {
        const q = searchTerm.trim().toLowerCase();
        return catalog.filter(function (product) {
            if (activeCategoryId !== '' && String(product.category_id) !== String(activeCategoryId)) {
                return false;
            }
            if (q === '') {
                return true;
            }
            return product.name.toLowerCase().includes(q) || product.code.toLowerCase().includes(q);
        });
    }

    function renderCatalog() {
        const items = filteredProducts();
        $catalogCount.text(items.length + (items.length === 1 ? ' product' : ' products'));

        if (items.length === 0) {
            $grid.html(
                '<div class="col-12">' +
                    '<div class="pos-empty-state text-center py-5">' +
                        '<i class="fa fa-search fa-2x text-muted mb-2"></i>' +
                        '<p class="mb-2 font-weight-medium">No matching products found</p>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary" id="pos-reset-filters">Reset filters</button>' +
                    '</div>' +
                '</div>'
            );
            return;
        }

        const html = items.map(function (product) {
            const inCart = cart.get(product.id);
            const inCartQty = inCart ? inCart.quantity : 0;
            const outOfStock = product.quantity <= 0;
            let stockBadgeClass = 'badge-soft-success';
            let stockLabel = product.quantity + ' in stock';

            if (outOfStock) {
                stockBadgeClass = 'badge-soft-danger';
                stockLabel = 'Out of stock';
            } else if (product.quantity <= 5) {
                stockBadgeClass = 'badge-soft-warning';
                stockLabel = 'Low: ' + product.quantity;
            }

            return (
                '<div class="col-xl-4 col-sm-6 mb-3">' +
                    '<div class="pos-product-card ' + (outOfStock ? 'is-out-of-stock' : '') + (inCartQty > 0 ? ' is-in-cart' : '') + '" data-product-id="' + product.id + '" tabindex="0" role="button" aria-label="Add ' + escapeHtml(product.name) + ' to cart">' +
                        '<div class="pos-product-thumb">' +
                            '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) + '">' +
                            '<span class="badge ' + stockBadgeClass + ' pos-stock-badge">' + escapeHtml(stockLabel) + '</span>' +
                            (inCartQty > 0 ? '<span class="badge badge-primary pos-cart-qty-pill">' + inCartQty + ' in cart</span>' : '') +
                        '</div>' +
                        '<div class="pos-product-body">' +
                            '<div class="pos-product-title" title="' + escapeHtml(product.name) + '">' + escapeHtml(product.name) + '</div>' +
                            '<div class="pos-product-meta">' +
                                '<span class="pos-sku">' + escapeHtml(product.code) + '</span>' +
                                '<span class="pos-category">' + escapeHtml(product.category_name) + '</span>' +
                            '</div>' +
                            '<div class="pos-product-footer">' +
                                '<span class="pos-price">' + money(product.sale_price) + '</span>' +
                                '<span class="pos-add-chip">' + (outOfStock ? 'Unavailable' : '<i class="fa fa-plus"></i> Add') + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        }).join('');

        $grid.html(html);
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalItems = 0;

        cart.forEach(function (entry) {
            totalItems += entry.quantity;
            subtotal = round2(subtotal + round2(entry.product.sale_price * entry.quantity));
        });

        let discount = round2(parseFloat($discount.val()) || 0);
        if (discount < 0) {
            discount = 0;
        }
        const discountExceeds = discount > subtotal && subtotal > 0;
        const effectiveDiscount = Math.min(discount, subtotal);

        let taxRate = round2(parseFloat($taxRate.val()) || 0);
        if (taxRate < 0) {
            taxRate = 0;
        } else if (taxRate > 100) {
            taxRate = 100;
        }

        const taxable = round2(subtotal - effectiveDiscount);
        const taxAmount = round2(taxable * (taxRate / 100));
        const grandTotal = round2(taxable + taxAmount);

        const rawPaid = $paidAmount.val();
        const paid = rawPaid === '' ? 0 : round2(parseFloat(rawPaid) || 0);
        const change = round2(Math.max(0, paid - grandTotal));

        return {
            totalItems: totalItems,
            subtotal: subtotal,
            discount: discount,
            discountExceeds: discountExceeds,
            taxRate: taxRate,
            taxAmount: taxAmount,
            grandTotal: grandTotal,
            paid: paid,
            hasPaidInput: rawPaid !== '',
            change: change,
        };
    }

    function renderCart() {
        if (cart.size === 0) {
            $cartBody.html(
                '<tr class="pos-cart-empty-row">' +
                    '<td colspan="5" class="text-center text-muted py-4">' +
                        '<i class="fa fa-shopping-basket fa-2x mb-2 d-block text-muted"></i>' +
                        'Cart is empty. Click a product card or scan a SKU code.' +
                    '</td>' +
                '</tr>'
            );
            $clearCartBtn.prop('disabled', true);
        } else {
            const rows = [];
            cart.forEach(function (entry) {
                const p = entry.product;
                const lineTotal = round2(p.sale_price * entry.quantity);
                rows.push(
                    '<tr data-product-id="' + p.id + '">' +
                        '<td>' +
                            '<div class="font-weight-medium text-dark pos-cart-item-name">' + escapeHtml(p.name) + '</div>' +
                            '<small class="text-muted">' + escapeHtml(p.code) + ' · Stock: ' + p.quantity + '</small>' +
                        '</td>' +
                        '<td class="text-nowrap">' + money(p.sale_price) + '</td>' +
                        '<td>' +
                            '<div class="pos-qty-control">' +
                                '<button type="button" class="btn btn-light btn-xs pos-qty-dec" aria-label="Decrease quantity"><i class="fa fa-minus"></i></button>' +
                                '<input type="number" class="form-control form-control-sm pos-qty-input" min="1" max="' + p.quantity + '" value="' + entry.quantity + '">' +
                                '<button type="button" class="btn btn-light btn-xs pos-qty-inc" aria-label="Increase quantity"><i class="fa fa-plus"></i></button>' +
                            '</div>' +
                        '</td>' +
                        '<td class="font-weight-semibold text-nowrap">' + money(lineTotal) + '</td>' +
                        '<td class="text-right">' +
                            '<button type="button" class="btn btn-link btn-xs text-danger p-0 pos-remove-item" title="Remove"><i class="fa fa-trash"></i></button>' +
                        '</td>' +
                    '</tr>'
                );
            });
            $cartBody.html(rows.join(''));
            $clearCartBtn.prop('disabled', false);
        }

        updateSummary();
    }

    function updateSummary() {
        const totals = calculateTotals();
        $cartItemsCount.text(totals.totalItems + (totals.totalItems === 1 ? ' item' : ' items'));
        $subtotalEl.text(money(totals.subtotal));
        $taxAmountEl.text(money(totals.taxAmount));
        $grandTotalEl.text(money(totals.grandTotal));
        $changeEl.text(money(totals.change));

        $paymentHint.removeClass('text-danger text-success').text('');
        if (cart.size > 0 && totals.discountExceeds) {
            $paymentHint.addClass('text-danger').text('Discount exceeds the subtotal.');
        } else if (cart.size > 0 && totals.paid + 0.00001 < totals.grandTotal) {
            const remaining = round2(totals.grandTotal - totals.paid);
            $paymentHint.addClass('text-danger').text('Balance due: ' + money(remaining));
        } else if (cart.size > 0 && totals.change > 0) {
            $paymentHint.addClass('text-success').text('Ready to complete sale.');
        }

        const canSubmit = !isSubmitting && cart.size > 0 && !totals.discountExceeds && (totals.paid + 0.00001 >= totals.grandTotal);
        $checkoutBtn.prop('disabled', !canSubmit);
    }

    function addToCart(productId) {
        const product = catalog.find(function (item) {
            return Number(item.id) === Number(productId);
        });
        if (!product || product.quantity <= 0) {
            showToast('Out of Stock', 'This product is currently out of stock.', 'warning');
            return;
        }

        const existing = cart.get(product.id);
        const nextQty = existing ? existing.quantity + 1 : 1;
        if (nextQty > product.quantity) {
            showToast('Stock Limit', 'Only ' + product.quantity + ' unit(s) available for ' + product.name + '.', 'warning');
            return;
        }

        cart.set(product.id, { product: product, quantity: nextQty });

        if ($paidAmount.val() === '' || Number($paidAmount.data('auto-synced')) === 1) {
            renderCart();
            syncExactPaid();
        } else {
            renderCart();
        }
        renderCatalog();
    }

    function syncExactPaid() {
        const totals = calculateTotals();
        $paidAmount.val(totals.grandTotal > 0 ? totals.grandTotal.toFixed(2) : '');
        $paidAmount.data('auto-synced', 1);
        updateSummary();
    }

    function setCheckoutError(message) {
        if (!message) {
            $errorAlert.addClass('d-none').text('');
            return;
        }
        $errorAlert.removeClass('d-none').text(message);
    }

    function refreshCatalogFromServer() {
        return $.getJSON(config.productsUrl).done(function (response) {
            if (response && response.status && Array.isArray(response.products)) {
                catalog = response.products;
                renderCatalog();
            }
        });
    }

    function resetSale() {
        cart.clear();
        $discount.val('0');
        $taxRate.val('0');
        $paidAmount.val('').data('auto-synced', 1);
        $notes.val('');
        setCheckoutError('');
        renderCart();
        renderCatalog();
    }

    // Search & filtering
    $search.on('input', function () {
        searchTerm = $(this).val();
        $searchClear.toggleClass('d-none', searchTerm === '');
        renderCatalog();
    });

    $search.on('keydown', function (e) {
        if (e.key !== 'Enter') {
            return;
        }
        e.preventDefault();
        const q = searchTerm.trim().toLowerCase();
        if (q === '') {
            return;
        }
        const exactCode = catalog.find(function (p) {
            return p.code.toLowerCase() === q;
        });
        if (exactCode) {
            addToCart(exactCode.id);
            $search.val('');
            searchTerm = '';
            $searchClear.addClass('d-none');
            renderCatalog();
            return;
        }
        const matches = filteredProducts();
        if (matches.length === 1) {
            addToCart(matches[0].id);
        }
    });

    $searchClear.on('click', function () {
        $search.val('').focus();
        searchTerm = '';
        $searchClear.addClass('d-none');
        renderCatalog();
    });

    $categoryPills.on('click', '.pos-category-pill', function () {
        $categoryPills.find('.pos-category-pill').removeClass('active');
        $(this).addClass('active');
        activeCategoryId = $(this).attr('data-category-id') || '';
        renderCatalog();
    });

    $(document).on('click', '#pos-reset-filters', function () {
        $search.val('');
        searchTerm = '';
        $searchClear.addClass('d-none');
        activeCategoryId = '';
        $categoryPills.find('.pos-category-pill').removeClass('active').first().addClass('active');
        renderCatalog();
    });

    // Catalog card click & keyboard enter
    $grid.on('click', '.pos-product-card', function () {
        addToCart($(this).attr('data-product-id'));
    });

    $grid.on('keydown', '.pos-product-card', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            addToCart($(this).attr('data-product-id'));
        }
    });

    // Cart quantity controls
    $cartBody.on('click', '.pos-qty-inc', function () {
        const productId = Number($(this).closest('tr').attr('data-product-id'));
        addToCart(productId);
    });

    $cartBody.on('click', '.pos-qty-dec', function () {
        const productId = Number($(this).closest('tr').attr('data-product-id'));
        const entry = cart.get(productId);
        if (!entry) {
            return;
        }
        if (entry.quantity <= 1) {
            cart.delete(productId);
        } else {
            entry.quantity -= 1;
        }
        renderCart();
        if (Number($paidAmount.data('auto-synced')) === 1) {
            syncExactPaid();
        }
        renderCatalog();
    });

    $cartBody.on('change', '.pos-qty-input', function () {
        const productId = Number($(this).closest('tr').attr('data-product-id'));
        const entry = cart.get(productId);
        if (!entry) {
            return;
        }
        let qty = parseInt($(this).val(), 10);
        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }
        if (qty > entry.product.quantity) {
            qty = entry.product.quantity;
            showToast('Stock Limit', 'Only ' + entry.product.quantity + ' unit(s) available.', 'warning');
        }
        entry.quantity = qty;
        renderCart();
        if (Number($paidAmount.data('auto-synced')) === 1) {
            syncExactPaid();
        }
        renderCatalog();
    });

    $cartBody.on('click', '.pos-remove-item', function () {
        const productId = Number($(this).closest('tr').attr('data-product-id'));
        cart.delete(productId);
        renderCart();
        if (Number($paidAmount.data('auto-synced')) === 1) {
            syncExactPaid();
        }
        renderCatalog();
    });

    $clearCartBtn.on('click', function () {
        resetSale();
    });

    $discount.on('input change', function () {
        updateSummary();
        if (Number($paidAmount.data('auto-synced')) === 1) {
            syncExactPaid();
        }
    });

    $taxRate.on('input change', function () {
        updateSummary();
        if (Number($paidAmount.data('auto-synced')) === 1) {
            syncExactPaid();
        }
    });

    $paidAmount.on('input', function () {
        $paidAmount.data('auto-synced', 0);
        updateSummary();
    });

    $exactBtn.on('click', function () {
        syncExactPaid();
    });

    // Quick-add Customer modal
    const $quickCustomerForm = $('#pos-quick-customer-form');
    const $quickCustomerErrors = $('#pos-quick-customer-errors');
    const $quickCustomerSubmit = $('#pos-quick-customer-submit');

    $('#pos-add-customer-modal').on('show.bs.modal', function () {
        $quickCustomerErrors.addClass('d-none').text('');
    });

    $quickCustomerForm.on('submit', function (e) {
        e.preventDefault();
        $quickCustomerErrors.addClass('d-none').text('');
        $quickCustomerSubmit.prop('disabled', true);

        $.ajax({
            url: config.storeCustomerUrl,
            type: 'POST',
            headers: { Accept: 'application/json' },
            data: $quickCustomerForm.serialize(),
            success: function (response) {
                if (response && response.status && response.customer) {
                    const c = response.customer;
                    const label = c.name + (c.phone ? ' (' + c.phone + ')' : '');
                    const newOption = new Option(label, c.id, true, true);
                    $customerSelect.append(newOption).trigger('change');
                    $quickCustomerForm[0].reset();
                    $('#pos-add-customer-modal').modal('hide');
                    showToast('Success', response.message || 'Customer added.', 'success');
                }
            },
            error: function (xhr) {
                let message = 'Unable to create customer.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $quickCustomerErrors.removeClass('d-none').text(message);
            },
            complete: function () {
                $quickCustomerSubmit.prop('disabled', false);
            }
        });
    });

    // Checkout submission
    $('#pos-checkout-form').on('submit', function (e) {
        e.preventDefault();
        if (isSubmitting || cart.size === 0) {
            return;
        }

        const totals = calculateTotals();
        if (totals.discountExceeds) {
            setCheckoutError('Discount cannot exceed the subtotal.');
            return;
        }
        if (totals.paid + 0.00001 < totals.grandTotal) {
            setCheckoutError('Paid amount must cover the grand total.');
            return;
        }

        setCheckoutError('');
        isSubmitting = true;
        const originalText = $checkoutBtn.html();
        $checkoutBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing Sale...');

        const items = [];
        cart.forEach(function (entry) {
            items.push({
                product_id: entry.product.id,
                quantity: entry.quantity,
            });
        });

        $.ajax({
            url: config.checkoutUrl,
            type: 'POST',
            headers: { Accept: 'application/json' },
            contentType: 'application/json',
            data: JSON.stringify({
                customer_id: $customerSelect.val() || null,
                items: items,
                discount: totals.discount,
                tax_rate: totals.taxRate,
                payment_method: $paymentMethod.val(),
                paid_amount: totals.paid,
                notes: $notes.val() || null,
            }),
            success: function (response) {
                if (response && response.status && response.order) {
                    $('#pos-success-order-no').text(response.order.order_no);
                    $('#pos-success-items').text(response.order.total_items);
                    $('#pos-success-total').text('$' + response.order.total);
                    $('#pos-success-paid').text('$' + response.order.paid_amount);
                    $('#pos-success-change').text('$' + response.order.change_amount);
                    $('#pos-success-receipt-btn').attr('href', response.receipt_url);
                    $('#pos-success-view-btn').attr('href', response.show_url);

                    resetSale();
                    refreshCatalogFromServer();
                    $('#pos-checkout-success-modal').modal('show');
                    showToast('Sale Completed', response.message, 'success');
                }
            },
            error: function (xhr) {
                let message = 'Unable to complete checkout. Please check quantities and payment.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                setCheckoutError(message);
                refreshCatalogFromServer();
            },
            complete: function () {
                isSubmitting = false;
                $checkoutBtn.html(originalText);
                updateSummary();
            }
        });
    });

    // Initialize Select2 & initial view
    $customerSelect.select2({
        placeholder: 'Walk-in / Guest Customer',
        allowClear: true,
        width: '100%',
    });

    $paidAmount.data('auto-synced', 1);
    renderCatalog();
    renderCart();
})(jQuery);
