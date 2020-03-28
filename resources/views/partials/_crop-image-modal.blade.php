<!-- Modal to crop image-->
<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="cropperModalTitle"
     aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropperModalLongTitle">Crop Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid p-0">
                    <img src="" class="img-fluid" id="croppedImage" alt="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="cancelCrop" data-dismiss="modal">Close</button>
                <button type="button" class="btn  btn-primary" id="cropImage">Set Image</button>
            </div>
        </div>
    </div>
</div>
{{-- End of Cropper Modal--}}