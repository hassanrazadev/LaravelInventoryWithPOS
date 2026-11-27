(function ($) {
    'use strict';

    const config = window.POS_CONFIG || {};
    const PRINTER_STORAGE_KEY = 'ims_pos_printer_settings_v3';

    let catalog = Array.isArray(config.products) ? config.products : [];
    const cart = new Map();
    let activeCategoryId = '';
    let searchTerm = '';
    let inStockOnly = false;
    let isSubmitting = false;
    let pendingPrintMode = 'none';
    let lastCompletedOrder = null;

    const $search = $('#pos-product-search');
    const $searchClear = $('#pos-search-clear');
    const $inStockBtn = $('#pos-instock-toggle');
    const $categoryPills = $('#pos-category-pills');
    const $catalogCount = $('#pos-catalog-count');
    const $grid = $('#pos-product-grid');
    const $cartList = $('#pos-cart-list');
    const $cartItemsCount = $('#pos-cart-items-count');
    const $clearCartBtn = $('#pos-clear-cart');
    const $discount = $('#pos-discount');
    const $taxRate = $('#pos-tax-rate');
    const $noteToggle = $('#pos-note-toggle');
    const $notesWrap = $('#pos-notes-wrap');
    const $notes = $('#pos-notes');
    const $subtotalEl = $('#pos-subtotal');
    const $taxAmountEl = $('#pos-tax-amount');
    const $grandTotalEl = $('#pos-grand-total');
    const $paymentMethod = $('#pos-payment-method');
    const $payPills = $('#pos-pay-pills');
    const $paidAmount = $('#pos-paid-amount');
    const $exactBtn = $('#pos-exact-btn');
    const $cashChips = $('#pos-cash-chips');
    const $changeEl = $('#pos-change-due');
    const $changePill = $('#pos-change-pill');
    const $customerSelect = $('#customer_id');
    const $checkoutBtn = $('#pos-checkout-btn');
    const $checkoutPrintBtn = $('#pos-checkout-print-btn');
    const $errorAlert = $('#pos-checkout-error');
    const $printerSummary = $('#pos-printer-summary');

    // Printer settings elements
    const $printerModal = $('#pos-printer-settings-modal');
    const $printerDriver = $('#printer_driver');
    const $printerHost = $('#printer_host');
    const $printerPort = $('#printer_port');
    const $printerHttpPath = $('#printer_http_path');
    const $printerHttpFormat = $('#printer_http_format');
    const $printerPaperWidth = $('#printer_paper_width');
    const $printerAutoprint = $('#printer_autoprint');
    const $printerOpenDrawer = $('#printer_open_drawer');
    const $printerDrawerField = $('#pos-printer-drawer-field');
    const $printerNetworkFields = $('#pos-printer-network-fields');
    const $printerHttpFields = $('#pos-printer-http-fields');
    const $printerTestStatus = $('#pos-printer-test-status');
    const $testPrintBtn = $('#pos-test-print-btn');

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

    function loadPrinterSettings() {
        const defaults = config.defaultPrinter || {};
        let saved = {};
        try {
            saved = JSON.parse(localStorage.getItem(PRINTER_STORAGE_KEY) || '{}');
        } catch (e) {
            saved = {};
        }

        const driver = ['tcp', 'http', 'browser'].includes(saved.driver)
            ? saved.driver
            : (['tcp', 'http', 'browser'].includes(defaults.driver) ? defaults.driver : 'browser');

        return {
            driver: driver,
            host: saved.host || defaults.host || '127.0.0.1',
            port: Number(saved.port || defaults.port || 9100),
            http_path: saved.http_path || defaults.http_path || '/',
            http_format: saved.http_format === 'text' ? 'text' : (defaults.http_format === 'text' ? 'text' : 'escpos'),
            paper_width: Number(saved.paper_width || defaults.paper_width || 80) === 58 ? 58 : 80,
            open_cash_drawer: saved.open_cash_drawer !== undefined
                ? Boolean(saved.open_cash_drawer)
                : (defaults.open_cash_drawer !== undefined ? Boolean(defaults.open_cash_drawer) : false),
            autoprint: Boolean(saved.autoprint),
        };
    }

    function savePrinterSettings(settings) {
        try {
            localStorage.setItem(PRINTER_STORAGE_KEY, JSON.stringify(settings));
        } catch (e) {
            // Ignore storage quota errors cleanly
        }
        renderPrinterSummary();
    }

    function renderPrinterSummary() {
        const s = loadPrinterSettings();
        if (s.driver === 'browser') {
            $printerSummary.text('Browser Print · ' + s.paper_width + 'mm');
            return;
        }
        const label = s.driver === 'http' ? 'HTTP ' : '';
        $printerSummary.text(label + s.host + ':' + s.port + ' · ' + s.paper_width + 'mm');
    }

    function syncPrinterModalFields() {
        const s = loadPrinterSettings();
        $printerDriver.val(s.driver);
        $printerHost.val(s.host);
        $printerPort.val(s.port);
        $printerHttpPath.val(s.http_path);
        $printerHttpFormat.val(s.http_format);
        $printerPaperWidth.val(String(s.paper_width));
        $printerAutoprint.prop('checked', s.autoprint);
        $printerOpenDrawer.prop('checked', s.open_cash_drawer);
        togglePrinterDriverFields();
    }

    function togglePrinterDriverFields() {
        const driver = $printerDriver.val();
        $printerNetworkFields.toggleClass('d-none', driver === 'browser');
        $printerHttpFields.toggleClass('d-none', driver !== 'http');
        $printerDrawerField.toggleClass('d-none', driver === 'browser');
        $testPrintBtn.toggleClass('d-none', driver === 'browser');
    }

    function readPrinterModalForm() {
        return {
            driver: $printerDriver.val(),
            host: ($printerHost.val() || '127.0.0.1').trim(),
            port: parseInt($printerPort.val(), 10) || 9100,
            http_path: ($printerHttpPath.val() || '/').trim(),
            http_format: $printerHttpFormat.val() || 'escpos',
            paper_width: parseInt($printerPaperWidth.val(), 10) === 58 ? 58 : 80,
            open_cash_drawer: $printerOpenDrawer.is(':checked'),
            autoprint: $printerAutoprint.is(':checked'),
        };
    }

    function filteredProducts() {
        const q = searchTerm.trim().toLowerCase();
        return catalog.filter(function (product) {
            if (inStockOnly && product.quantity <= 0) {
                return false;
            }
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
                '<div class="pos-empty-state text-center py-5">' +
                    '<i class="fa fa-search fa-2x text-muted mb-2"></i>' +
                    '<p class="mb-2 font-weight-medium">No matching products found</p>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary" id="pos-reset-filters">Reset filters</button>' +
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
                            '<span class="pos-add-chip">' + (outOfStock ? 'Out' : '<i class="fa fa-plus"></i> Add') + '</span>' +
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
            $cartList.html(
                '<div class="pos-cart-empty">' +
                    '<i class="fa fa-shopping-basket fa-2x mb-2 text-muted"></i>' +
                    '<div class="font-weight-medium text-dark">Cart is empty</div>' +
                    '<small class="text-muted">Click any product card or scan a SKU code to start.</small>' +
                '</div>'
            );
            $clearCartBtn.prop('disabled', true);
        } else {
            const rows = [];
            cart.forEach(function (entry) {
                const p = entry.product;
                const lineTotal = round2(p.sale_price * entry.quantity);
                rows.push(
                    '<div class="pos-cart-item" data-product-id="' + p.id + '">' +
                        '<div class="pos-cart-item-top">' +
                            '<div class="pos-cart-item-name" title="' + escapeHtml(p.name) + '">' + escapeHtml(p.name) + '</div>' +
                            '<div class="d-flex align-items-center" style="gap: 8px;">' +
                                '<span class="pos-cart-item-total">' + money(lineTotal) + '</span>' +
                                '<button type="button" class="pos-cart-remove pos-remove-item" title="Remove item" aria-label="Remove ' + escapeHtml(p.name) + '">' +
                                    '<i class="fa fa-times"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="pos-cart-item-bottom">' +
                            '<small class="text-muted">' + escapeHtml(p.code) + ' · ' + money(p.sale_price) + ' ea · ' + p.quantity + ' left</small>' +
                            '<div class="pos-qty-control">' +
                                '<button type="button" class="btn btn-light btn-xs pos-qty-dec" aria-label="Decrease quantity"><i class="fa fa-minus"></i></button>' +
                                '<input type="number" class="form-control form-control-sm pos-qty-input" min="1" max="' + p.quantity + '" value="' + entry.quantity + '">' +
                                '<button type="button" class="btn btn-light btn-xs pos-qty-inc" aria-label="Increase quantity"><i class="fa fa-plus"></i></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            });
            $cartList.html(rows.join(''));
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

        $('#pos-checkout-btn-label').text(
            totals.grandTotal > 0 ? 'Complete Sale · ' + money(totals.grandTotal) : 'Complete Sale'
        );

        $changePill.removeClass('text-danger text-success');
        if (cart.size > 0 && totals.discountExceeds) {
            $changePill.addClass('text-danger').html('Discount too high');
        } else if (cart.size > 0 && totals.paid + 0.00001 < totals.grandTotal) {
            const due = round2(totals.grandTotal - totals.paid);
            $changePill.addClass('text-danger').html('Due: <strong>' + money(due) + '</strong>');
        } else {
            $changePill.addClass('text-success').html('Change: <strong id="pos-change-due">' + money(totals.change) + '</strong>');
        }

        const canSubmit = !isSubmitting && cart.size > 0 && !totals.discountExceeds && (totals.paid + 0.00001 >= totals.grandTotal);
        $checkoutBtn.prop('disabled', !canSubmit);
        $checkoutPrintBtn.prop('disabled', !canSubmit);
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
        $notesWrap.addClass('d-none');
        setCheckoutError('');
        renderCart();
        renderCatalog();
    }

    function sendThermalPrintJob(printThermalUrl, receiptUrl, $statusTarget) {
        const settings = loadPrinterSettings();

        if (settings.driver === 'browser') {
            window.open(receiptUrl + '?width=' + settings.paper_width + '&autoprint=1', '_blank');
            if ($statusTarget && $statusTarget.length) {
                $statusTarget
                    .removeClass('d-none alert-danger')
                    .addClass('alert-success')
                    .text('Opened ' + settings.paper_width + 'mm thermal print preview.');
            }
            return $.Deferred().resolve().promise();
        }

        if ($statusTarget && $statusTarget.length) {
            $statusTarget
                .removeClass('d-none alert-success alert-danger')
                .addClass('alert-info')
                .text('Sending receipt to ' + settings.host + ':' + settings.port + '...');
        }

        return $.ajax({
            url: printThermalUrl,
            type: 'POST',
            headers: { Accept: 'application/json' },
            contentType: 'application/json',
            data: JSON.stringify(settings),
            success: function (res) {
                if ($statusTarget && $statusTarget.length) {
                    $statusTarget
                        .removeClass('d-none alert-info alert-success alert-danger')
                        .addClass(res.status ? 'alert-success' : 'alert-danger')
                        .text(res.message);
                }
                showToast(res.status ? 'Receipt Printed' : 'Printer Error', res.message, res.status ? 'success' : 'error');
            },
            error: function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to reach thermal printer.';
                if ($statusTarget && $statusTarget.length) {
                    $statusTarget.removeClass('d-none alert-info alert-success').addClass('alert-danger').text(msg);
                }
                showToast('Printer Error', msg, 'error');
            }
        });
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

    $inStockBtn.on('click', function () {
        inStockOnly = !inStockOnly;
        $(this).toggleClass('active btn-primary', inStockOnly).toggleClass('btn-light', !inStockOnly);
        $(this).attr('aria-pressed', String(inStockOnly));
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
        inStockOnly = false;
        $inStockBtn.removeClass('active btn-primary').addClass('btn-light').attr('aria-pressed', 'false');
        activeCategoryId = '';
        $categoryPills.find('.pos-category-pill').removeClass('active').first().addClass('active');
        renderCatalog();
    });

    // Catalog click & keyboard
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
    $cartList.on('click', '.pos-qty-inc', function () {
        const productId = Number($(this).closest('.pos-cart-item').attr('data-product-id'));
        addToCart(productId);
    });

    $cartList.on('click', '.pos-qty-dec', function () {
        const productId = Number($(this).closest('.pos-cart-item').attr('data-product-id'));
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

    $cartList.on('change', '.pos-qty-input', function () {
        const productId = Number($(this).closest('.pos-cart-item').attr('data-product-id'));
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

    $cartList.on('click', '.pos-remove-item', function () {
        const productId = Number($(this).closest('.pos-cart-item').attr('data-product-id'));
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

    $noteToggle.on('click', function () {
        $notesWrap.toggleClass('d-none');
        if (! $notesWrap.hasClass('d-none')) {
            $notes.focus();
        }
    });

    // Payment pills & quick cash chips
    $payPills.on('click', '.pos-pay-pill', function () {
        const method = $(this).attr('data-method');
        $payPills.find('.pos-pay-pill').removeClass('active');
        $(this).addClass('active');
        $paymentMethod.val(method);
        $cashChips.toggleClass('d-none', method !== 'cash');

        if (method !== 'cash') {
            syncExactPaid();
        }
    });

    $cashChips.on('click', '.pos-cash-chip', function () {
        const tender = $(this).attr('data-tender');
        if (tender === 'exact') {
            syncExactPaid();
            return;
        }

        const addAmount = parseFloat($(this).attr('data-add')) || 0;
        const currentBase = Number($paidAmount.data('auto-synced')) === 1 ? 0 : (parseFloat($paidAmount.val()) || 0);
        $paidAmount.data('auto-synced', 0).val(round2(currentBase + addAmount).toFixed(2));
        updateSummary();
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

    // Thermal Printer settings modal
    $printerModal.on('show.bs.modal', function () {
        $printerTestStatus.addClass('d-none').removeClass('alert-success alert-danger alert-info').text('');
        syncPrinterModalFields();
    });

    $printerDriver.on('change', togglePrinterDriverFields);

    $('#pos-printer-settings-form').on('submit', function (e) {
        e.preventDefault();
        const settings = readPrinterModalForm();
        savePrinterSettings(settings);
        $printerModal.modal('hide');
        showToast('Printer Settings Saved', 'Thermal receipt target updated.', 'success');
    });

    $testPrintBtn.on('click', function () {
        const settings = readPrinterModalForm();
        savePrinterSettings(settings);
        const orig = $testPrintBtn.html();
        $testPrintBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Sending...');
        $printerTestStatus.addClass('d-none').removeClass('alert-success alert-danger alert-info').text('');

        $.ajax({
            url: config.printerTestUrl,
            type: 'POST',
            headers: { Accept: 'application/json' },
            contentType: 'application/json',
            data: JSON.stringify(settings),
            success: function (res) {
                $printerTestStatus
                    .removeClass('d-none alert-success alert-danger')
                    .addClass(res.status ? 'alert-success' : 'alert-danger')
                    .text(res.message);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Unable to send test print.';
                $printerTestStatus.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
            },
            complete: function () {
                $testPrintBtn.prop('disabled', false).html(orig);
            }
        });
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

    // Checkout submission (Complete Sale or Pay & Print)
    $checkoutPrintBtn.on('click', function () {
        pendingPrintMode = 'print';
        $('#pos-checkout-form').trigger('submit');
    });

    $('#pos-checkout-form').on('submit', function (e) {
        e.preventDefault();
        if (isSubmitting || cart.size === 0) {
            pendingPrintMode = 'none';
            return;
        }

        const totals = calculateTotals();
        if (totals.discountExceeds) {
            setCheckoutError('Discount cannot exceed the subtotal.');
            pendingPrintMode = 'none';
            return;
        }
        if (totals.paid + 0.00001 < totals.grandTotal) {
            setCheckoutError('Paid amount must cover the grand total.');
            pendingPrintMode = 'none';
            return;
        }

        const shouldPrint = pendingPrintMode === 'print' || loadPrinterSettings().autoprint;
        pendingPrintMode = 'none';

        setCheckoutError('');
        isSubmitting = true;
        $checkoutBtn.prop('disabled', true);
        $checkoutPrintBtn.prop('disabled', true);

        const activeBtn = shouldPrint ? $checkoutPrintBtn : $checkoutBtn;
        const originalActiveHtml = activeBtn.html();
        activeBtn.html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

        const items = [];
        cart.forEach(function (entry) {
            items.push({
                product_id: entry.product.id,
                quantity: entry.quantity,
            });
        });

        const settings = loadPrinterSettings();

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
                    lastCompletedOrder = response;

                    $('#pos-success-order-no').text(response.order.order_no);
                    $('#pos-success-items').text(response.order.total_items);
                    $('#pos-success-total').text('$' + response.order.total);
                    $('#pos-success-paid').text('$' + response.order.paid_amount);
                    $('#pos-success-change').text('$' + response.order.change_amount);
                    $('#pos-success-receipt-btn').attr('href', response.receipt_url + '?width=' + settings.paper_width);
                    $('#pos-success-view-btn').attr('href', response.show_url);
                    $('#pos-success-thermal-btn').html(
                        settings.driver === 'browser'
                            ? '<i class="fa fa-print mr-1"></i> Print Receipt'
                            : '<i class="fa fa-bolt mr-1"></i> Thermal Print'
                    );

                    const $successPrintStatus = $('#pos-success-print-status');
                    $successPrintStatus.addClass('d-none').removeClass('alert-success alert-danger alert-info').text('');

                    resetSale();
                    refreshCatalogFromServer();
                    $('#pos-checkout-success-modal').modal('show');
                    showToast('Sale Completed', response.message, 'success');

                    if (shouldPrint) {
                        sendThermalPrintJob(response.print_thermal_url, response.receipt_url, $successPrintStatus);
                    }
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
                activeBtn.html(originalActiveHtml);
                updateSummary();
            }
        });
    });

    $('#pos-success-thermal-btn').on('click', function () {
        if (!lastCompletedOrder) {
            return;
        }
        sendThermalPrintJob(
            lastCompletedOrder.print_thermal_url,
            lastCompletedOrder.receipt_url,
            $('#pos-success-print-status')
        );
    });

    // Initialize Select2 & initial POS state
    $customerSelect.select2({
        placeholder: 'Walk-in / Guest Customer',
        allowClear: true,
        width: '100%',
    });

    $paidAmount.data('auto-synced', 1);
    renderPrinterSummary();
    renderCatalog();
    renderCart();
})(jQuery);
