<div class="modal fade in" id="modal-crop_img">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.uploadimg}</h4>
            </header>

            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-6">
                        <img src="" alt="" class="js-crop_img">
                    </div>
                    <div class="col-xs-6 help-block js-crop_img-help">
                    </div>
                </div>
                <!-- div class="clearfix"></div -->
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-default js-cancel">
                    {$aLang.settings_profile_avatar_resize_cancel}
                </button>
                <button type="submit" class="btn btn-success js-confirm">
                    {$aLang.settings_profile_avatar_resize_apply}
                </button>
            </div>

        </div>
    </div>
</div>
