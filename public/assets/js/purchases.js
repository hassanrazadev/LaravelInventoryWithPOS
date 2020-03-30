callSelect2Plugin();

$(document).on('click', '.add-product', function (e) {
    let lastProductRow = $('.product-row:last');
    let index = lastProductRow[0] ? parseInt($(lastProductRow).data('index')+1) : 0;
    let url = $(this).data('url') + '/' + index;
    $.get(url, function (response) {
        $('.product-rows-end').before(response);
        callSelect2Plugin();
        $('[data-toggle="tooltip"]').tooltip();
    })
});

$(document).on('click', '.remove-product', function (e) {
    $('.product-row[data-index="'+$(this).data('index')+'"]').remove();
});

function callSelect2Plugin() {
    $('.select2_select').select2({
        placeholder: function () {
            $(this).data('placeholder');
        },
        allowClear: true
    });
}

$('#purchaseForm').on('submit', function (e) {
    // setting rules for dynamically adding fields
    $('.product_id').each(function () {
        $(this).rules('add', {
            required: !0,
        })
    });
    $('.quantity').each(function () {
        $(this).rules('add', {
            required: !0,
            min: 1,
        })
    });
    $('.unit_price').each(function () {
        $(this).rules('add', {
            required: !0,
            min: 1,
        })
    });
    $('.sub_total').each(function () {
        $(this).rules('add', {
            required: !0,
            min: 1,
        })
    });
});

// validating product fields to prevent repetition
$.validator.addMethod("unique", function(value, element) {
    let parentForm = $(element).closest('form');
    let timeRepeated = 0;
    if (value !== '') {
        $(parentForm.find('select.unique')).each(function () {
            if ($(this).val() === value) {
                timeRepeated++;
            }
        });
    }
    return timeRepeated === 1 || timeRepeated === 0;

}, "This product was already selected.");

// form validation
$('#purchaseForm').validate({
    rules: {
        total: {
            required: !0,
            min: 1,
        },
    },
    errorClass: "help-block error",
    highlight: function (e) {
        $(e).closest(".form-group.row").addClass("has-error")
    },
    unhighlight: function (e) {
        $(e).closest(".form-group.row").removeClass("has-error")
    },
    errorPlacement: function(error, element) {
        if (element.hasClass('select2_select')) {
            error.insertAfter(element.siblings('span'));
        } else {
            error.insertAfter(element);
        }
    }
});

$(document).on('keyup change', '.unit_price, .quantity', function (e) {
    let unitPrice = $(this).parents('.product-row').find('.unit_price');
    let quantity = $(this).parents('.product-row').find('.quantity');
    let subTotal = (unitPrice[0] ? parseFloat($(unitPrice).val()) : 0) * (quantity[0] ? parseFloat($(quantity).val()) : 0);
    $(this).parents('.product-row').find('.sub_total').val(subTotal);
    $('.sub_total').trigger('change');
});

$(document).on('propertychange change click keyup input paste', '.sub_total', function (e) {
    let total = 0;
    $('.sub_total').each(function () {
        total += parseFloat($(this).val()) ? parseFloat($(this).val()) : 0;
    });
    $('#total').val(total);
});
