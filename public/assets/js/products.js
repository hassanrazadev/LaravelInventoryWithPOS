$('.select2_select').select2({
    placeholder: "Select parent category",
    allowClear: true
});

$('#productForm').validate({
    rules: rules,
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

//
let imagePreview = $('#productImagePreview');
let imagePicker = $('#product_image_picker');
let imageField = $('#product_image');

initImageCropperModal(imagePreview, imagePicker, imageField);

$(document).on('change', '#product_image_picker', function (e) {
    let imagePath = $(this).val();
    let allowedExtensions = /(\.jpg|\.png|\.jpeg)$/i;

    if (!allowedExtensions.exec(imagePath)) {
        alert('Image format is not correct');
        $(this).val('');
        $(imagePreview).attr('src', defaultImage);
        return false;
    } else {
        showImageCropper(e);
    }
});

function createSlug(value) {
    return value
        .toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-')
        ;
}
$(document).on('keyup', '#product_name', function (e) {
    $('#product_slug').val(createSlug($(this).val()));
});