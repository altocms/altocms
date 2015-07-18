{*{$sTargetType="topic"}*}
<script>
    $(function () {
        $('.js-alto-topic-uploader')
                .altoMultiUploader({
                    target: '{$sTargetType}',
                    tpl: '.js-topic-file-tpl',
                    list: '>.js-files-tpl',
                    upload: '.js-upload-topic',
                    form: '.js-alto-topic-uploader-form',
                    tmp: '{if isset($bTmp)}{$bTmp}{else}true{/if}',
                    preview_width: 60,
                    preview_height: 60,
                    onComplete: function (evt, uiEvt) {
                        var uploaded = $('.js-alto-topic-uploader').altoMultiUploader('getUploaded');
                        $.each(uploaded, function(key, url) {
                            console.log(url);
                            var $param = $('#aim-params');
                                align = $param.find('[name=align]').val(),
                                title = $param.find('[name=title]').val(),
                                size = parseInt($param.find('[name=img_width]').val(), 10);

                            align = (align == 'center') ? ' class="image-center"' : ((align == '') ? '' : 'align="' + align + '" ');
                            size = (size == 0) ? '' : ' width="' + size + '%" ';
                            title = (title == '') ? '' : ' title="' + title + '"' + ' alt="' + title + '" ';

                            var html = '<img src="' + url + '"' + title + align + size + ' />';
                            if (tinymce) {
                                ls.insertToEditor(html);
                            } else {
                                $.markItUp({
                                    replaceWith: html
                                });
                            }
                            $('.js-alto-topic-uploader .js-alto-multi-uploader-list').html('');
                            $('#js-alto-image-manager').modal('hide');
                        })
                    },
                    result_template: '.js-alto-topic-uploader-template',
                    maxSize: '{C::Get("module.topic.photoset.photo_max_size")/1024}',
                    maxWidth: '{C::Get("module.uploader.images.default.max_width")}',
                    maxHeight: '{C::Get("module.uploader.images.default.max_height")}',
                    maxSizeError: '{$aLang.topic_field_file_upload_err_size|ls_lang:"size%%{C::Get('module.topic.photoset.photo_max_size')/1024}Mb"}',
                    maxWidthError: '{$aLang.topic_field_file_upload_err_size|ls_lang:"size%%{C::Get('module.uploader.images.default.max_width')}x{C::Get('module.uploader.images.default.max_height')}px"}',
                    maxHeightError: '{$aLang.topic_field_file_upload_err_size|ls_lang:"size%%{C::Get('module.uploader.images.default.max_width')}x{C::Get('module.uploader.images.default.max_height')}px"}'
                });
    });
</script>
{if isset($_aRequest.topic_id) && $_aRequest.topic_id}{$sTargetId=$_aRequest.topic_id}{else}{$sTargetId=0}{/if}

<script type="text/template" class="js-alto-topic-uploader-template">
    {var name="sTemplate"}
    <div class="col-md-12 mab6" id="uploader_item_ID">
        <div class="row">
            <div class="col-md-2">
                <img src="uploader_item_SRC" width="100%" alt="image"/>
            </div>
            <div class="col-md-10">
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <div class="js-file-name">{$aLang.uploader_upload_success}</div>
            </div>
        </div>
    </div>
    {/var}
</script>

<div class="add-photo">
    <div class="img-container">
        <div>
            {* ÁËÎÊ ÇÀÃÐÓÇÊÈ ÈÇÎÁÐÀÆÅÍÈß *}

            <div class="js-alto-topic-uploader js-topic-photoset"
                 data-target="{$sTargetType}"
                 data-target-id="{$sTargetId}"
                 data-lang-cover-done="{$aLang.topic_photoset_is_preview}"
                 data-lang-cover-need="{$aLang.topic_photoset_mark_as_preview}"
                 data-cover="{if isset($_aRequest.topic_main_photo)}{$_aRequest.topic_main_photo}{else}0{/if}"
                 data-preview-crop="60x60-crop">

                <input class="js-alto-multi-uploader-target-preview"  name="target_preview" type="hidden"/>



                {* Ôîðìà çàãðóçêè èçîáðàæåíèé *}
                <div class="js-alto-topic-uploader-form clearfix">

                        <div class="js-alto-multi-uploader-list list-unstyled row"></div>
                        {literal}
                            <div class="js-files-tpl row">
                                <div class="js-topic-file-tpl js-autoremove col-md-12" data-id="<%=uid%>" title="<%-name%>, <%-sizeText%>">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="js-file-preview">
                                                <div class="js-file-image"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 0"></div>
                                            </div>
                                            <div class="js-file-name"><%-name%></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/literal}


                    <div>
                        <div class="col-md-12 js-uploader-picker">
                            <div class="small text-muted topic-photo-upload-rules pull-right">
                                <div class="js-uploader-picker-supported"></div>
                            </div>
                            <div class="btn btn-default btn-sm js-add js-fileapi-wrapper">
                                <span>{$aLang.topic_photoset_upload_add}</span>
                                <input type="file" name="uploader-upload-image">
                            </div>
                            <div class="js-upload-topic btn btn-default btn-sm">
                                <span>{$aLang.topic_photoset_upload_choose}</span>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>

    </div>

</div>
<br/>
