$('.select2_select').select2({
    placeholder: "Select parent category",
    allowClear: true
});

$('#categoryForm').validate({
    rules: rules,
    errorClass: "help-block error",
    highlight: function (e) {
        $(e).closest(".form-group.row").addClass("has-error")
    },
    unhighlight: function (e) {
        $(e).closest(".form-group.row").removeClass("has-error")
    },
});

$(document).on('keyup', '#category_name', function (e) {
    $('#category_slug').val(createSlug($(this).val()));
});

function createSlug(value) {
    return value
        .toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-')
        ;
}

let croppedImage, cropperModal;
let cropper; //Store cropper object
let categoryImagePreview = $('#categoryImagePreview');
let defaultImage = $(categoryImagePreview).attr('src');

$(document).ready(function (e) {
    croppedImage = document.getElementById('croppedImage'); //image cropper in modal
    cropperModal = $('#cropperModal'); //Modal to show cropper

    $(cropperModal).on('shown.bs.modal', function () {
        cropper = new Cropper(croppedImage, {
            aspectRatio: 1.0,
            viewMode: 3,
        });
    }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    });

    $(document).on('click', '#cropImage', function () {
        let canvas;
        $(cropperModal).modal('hide');
        if (cropper) {
            canvas = cropper.getCroppedCanvas();
            //Set image preview
            $('#category_image').val(canvas.toDataURL());
            $('#category_image-error').remove();
            $(categoryImagePreview).attr('src', canvas.toDataURL());
        }
    });

    $(document).on('click', '#cancelCrop', function (e) {
        $('#category_image_picker').val('');
        $(categoryImagePreview).attr('src', defaultImage);
    });

});

/**
 * show image cropper in modal after picking image
 * @param e
 */
function showImageCropper(e) {
    let files = e.target.files;
    let done = function (url) {
        $(croppedImage).attr('src', url);
        $(cropperModal).modal('show');
    };

    let reader, file;
    if (files && files.length > 0) {
        file = files[0];
        if (URL) {
            done(URL.createObjectURL(file));
        } else if (FileReader) {
            reader = new FileReader();
            reader.onload = function (e) {
                done(reader.result);
            };
            reader.readAsDataURL(file);
        }
    }
}

$(document).on('change', '#category_image_picker', function (e) {
    let imagePath = $(this).val();
    let allowedExtensions = /(\.jpg|\.png|\.jpeg)$/i;

    if (!allowedExtensions.exec(imagePath)) {
        alert('Image format is not correct');
        $(this).val('');
        $(categoryImagePreview).attr('src', defaultImage);
        return false;
    } else {
        showImageCropper(e);
    }
});