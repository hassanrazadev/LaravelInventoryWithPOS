let croppedImage, cropperModal;
let cropper; //Store cropper object
let defaultImage;

/**
 * init image cropper modal
 * @param imagePreview
 * @param imagePicker
 * @param imageField
 */
function initImageCropperModal(imagePreview, imagePicker, imageField){
    defaultImage = $(imagePreview).attr('src');
    croppedImage = document.getElementById('croppedImage'); //image cropper in modal
    cropperModal = $('#cropperModal'); //Modal to show cropper

    $(cropperModal).on('shown.bs.modal', function () {
        cropper = new Cropper(croppedImage, {
            aspectRatio: 1,
            viewMode: 1,
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
            $(imageField).val(canvas.toDataURL());
            $(imageField).siblings('.help-block.error').remove();
            $(imagePreview).attr('src', canvas.toDataURL());
        }
    });

    $(document).on('click', '#cancelCrop', function (e) {
        $(imagePicker).val('');
        $(imagePreview).attr('src', defaultImage);
    });
}

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