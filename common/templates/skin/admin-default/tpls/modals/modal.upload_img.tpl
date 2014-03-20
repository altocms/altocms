<div class="modal fade in" id="modal-upload_img">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.uploadimg}</h4>
            </header>

            <div class="modal-body">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#" data-toggle="tab" data-target=".js-pane-upload_img_pc">{$aLang.uploadimg_from_pc}</a></li>
                    <li>
                        <a href="#" data-toggle="tab" data-target=".js-pane-upload_img_link">{$aLang.uploadimg_from_link}</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active js-pane-upload_img_pc">
                        <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_pc"
                              onsubmit="return false;" class="js-block-upload-img-content">
                            <div class="form-group">
                                <label for="img_file">{$aLang.uploadimg_file}</label>
                                <input type="file" name="img_file" id="img_file" value=""/>
                            </div>

                            {hook run="uploadimg_source"}

                            <div class="form-group">
                                <label for="form-image-align">{$aLang.uploadimg_align}</label>
                                <select name="align" id="form-image-align" class="form-control">
                                    <option value="">{$aLang.uploadimg_align_no}</option>
                                    <option value="left">{$aLang.uploadimg_align_left}</option>
                                    <option value="right">{$aLang.uploadimg_align_right}</option>
                                    <option value="center">{$aLang.uploadimg_align_center}</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="form-image-title">{$aLang.uploadimg_title}</label>
                                <input type="text" name="title" id="form-image-title" value="" class="form-control"/>
                            </div>

                            {hook run="uploadimg_additional"}

                            <button type="submit" class="btn btn-success"
                                    onclick="ls.ajaxUploadImg(this,'{$sToLoad}');">{$aLang.uploadimg_submit}</button>
                            <button type="submit" class="btn btn-default">{$aLang.uploadimg_cancel}</button>
                        </form>
                    </div>

                    <div class="tab-pane js-pane-upload_img_link">
                        <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_link"
                              onsubmit="return false;" class="tab-content js-block-upload-img-content">
                            <div class="form-group">
                                <label for="img_file">{$aLang.uploadimg_url}</label>
                                <input type="text" name="img_url" id="img_url" value="http://" class="form-control"/>
                            </div>

                            <div class="form-group">
                                <label for="form-image-url-align">{$aLang.uploadimg_align}</label>
                                <select name="align" id="form-image-url-align" class="form-control">
                                    <option value="">{$aLang.uploadimg_align_no}</option>
                                    <option value="left">{$aLang.uploadimg_align_left}</option>
                                    <option value="right">{$aLang.uploadimg_align_right}</option>
                                    <option value="center">{$aLang.uploadimg_align_center}</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="form-image-url-title">{$aLang.uploadimg_title}</label>
                                <input type="text" name="title" id="form-image-url-title" value="" class="form-control"/>
                            </div>

                            {hook run="uploadimg_link_additional"}

                            <button type="submit" class="btn btn-success"
                                    onclick="ls.topic.insertImageToEditor(jQuery('#img_url').val(),jQuery('#form-image-url-align').val(),jQuery('#form-image-url-title').val());">{$aLang.uploadimg_link_submit_paste}</button>
                            {$aLang._or}
                            <button type="submit" class="btn btn-success"
                                    onclick="ls.ajaxUploadImg('block_upload_img_content_link','{$sToLoad}');">{$aLang.uploadimg_link_submit_load}</button>
                            <button type="submit" class="btn btn-default">{$aLang.uploadimg_cancel}</button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
