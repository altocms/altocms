<script>
    $(function () {
        $('.js-alto-multi-uploader')
                .altoMultiUploader({
                    {if isset($sFormId)}submitForm: "{$sFormId}",{/if}
                    photoset: '.js-alto-multi-photoset-list',
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
{$sTargetType="photoset"}

<script type="text/template" class="js-alto-multi-uploader-template">
    {var name="sTemplate"}
        <li id="uploader_item_ID">
            <div class="row">
                <div class="col-sm-4">
                    <img src="uploader_item_SRC" width="100%" alt="image"/>
                </div>
                <div class="col-sm-20">
                    <textarea onblur="$('.js-alto-multi-uploader').altoMultiUploader({ description: 'ID' }); return false;"
                              class="form-control"></textarea>
                    <a href="#"
                       onclick="$('.js-alto-multi-uploader').altoMultiUploader({ remove: 'ID' }); return false;"
                       class="link link-lead link-red-blue js-uploader-item-delete"><i class="fa fa-times"></i>&nbsp;{$aLang.topic_photoset_photo_delete}</a>
                    <span class="photo-preview-state">
                        <a href="#"
                           onclick="$('.js-alto-multi-uploader').altoMultiUploader({ cover: 'ID' }); return false;"
                           class="link link-lead link-blue js-uploader-item-cover PHOTOSET-IS-COVER">
                            <span class="marked-as-cover"><i class="fa fa-check-square-o"></i> {$aLang.topic_photoset_is_preview}</span>
                            <span class="mark-as-cover"><i class="fa fa-square-o"></i> {$aLang.topic_photoset_mark_as_preview}</span>
                        </a>
                    </span>
                </div>
            </div>
        </li>
    {/var}
</script>

{if count($aPhotos)}
    {$ImagesList=""}
    {foreach $aPhotos as $oPhoto}
        {var name="sPhotosetItem" cache=true}
            <li id="uploader_item_{$oPhoto->getMResourceId()}">
                <div class="row">
                    <div class="col-sm-4">
                        <img src="{$oPhoto->getWebPath('100crop')}" width="100%" alt="image"/>
                    </div>
                    <div class="col-sm-20">
                    <textarea onblur="$('.js-alto-multi-uploader').altoMultiUploader({ description: '{$oPhoto->getMResourceId()}' }); return false;"
                              class="form-control">{$oPhoto->getDescription()}</textarea>
                        <a href="#"
                           onclick="$('.js-alto-multi-uploader').altoMultiUploader({ remove: '{$oPhoto->getMResourceId()}' }); return false;"
                           class="link link-lead link-red-blue js-uploader-item-delete"><i class="fa fa-times"></i>&nbsp;{$aLang.topic_photoset_photo_delete}</a>
                    <span class="photo-preview-state">
                        <a href="#"
                           onclick="$('.js-alto-multi-uploader').altoMultiUploader({ cover: '{$oPhoto->getMResourceId()}' }); return false;"
                           class="link link-lead link-blue js-uploader-item-cover {if $oPhoto->IsCover()}photoset-is-cover{/if}">
                            <span class="marked-as-cover"><i class="fa fa-check-square-o"></i> {$aLang.topic_photoset_is_preview}</span>
                            <span class="mark-as-cover"><i class="fa fa-square-o"></i> {$aLang.topic_photoset_mark_as_preview}</span>
                        </a>
                    </span>
                    </div>
                </div>
            </li>
        {/var}
        {$ImagesList="$ImagesList $sPhotosetItem"}
    {/foreach}
{else}
    {$ImagesList = {imgs target-id=$sTargetId target-type=$sTargetType template=$sTemplate crop='100fit'}}
{/if}

<div class="add-photo">
    <div class="clearfix toggle-block">
        <a href="#"
           onclick="$(this).toggleClass('active').parent().next().slideToggle(100); return false;"
           class="pull-right toggle-link link link-lead link-blue"><i class="fa fa-plus-circle"></i>{$aLang.topic_toggle_images}</a>
    </div>
    <div style="display: {if $ImagesList}block{else}none{/if};" class="img-container">
        <div>
            {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}

            <div class="js-alto-multi-uploader js-topic-photoset"
                 data-target="{$sTargetType}"
                 data-target-id="{$sTargetId}"
                 data-lang-cover-done="{$aLang.topic_photoset_is_preview}"
                 data-lang-cover-need="{$aLang.topic_photoset_mark_as_preview}"
                 data-cover="{if isset($_aRequest.topic_main_photo)}{$_aRequest.topic_main_photo}{else}0{/if}"
                 data-preview-crop="400fit">

                <input class="js-alto-multi-uploader-target-preview"  name="target_preview" type="hidden"/>

                <ul class="js-alto-multi-photoset-list js-alto-multi-uploader-list list-unstyled" {if !$ImagesList}style="display: none;"{/if}>
                    {$ImagesList}
                </ul>

                {* Форма загрузки изображений *}
                <div class="js-alto-multi-uploader-form">
                    {literal}
                    <div class="js-files row">
                        <div class="js-file-tpl js-autoremove col-md-6" data-id="<%=uid%>" title="<%-name%>, <%-sizeText%>">
                            <div class="thumbnail">
                                <div data-fileapi="file.remove" class="js-file-delete"><i class="fa fa-times"></i></div>
                                <div class="js-file-reload"><i class="fa fa-refresh"></i></div>
                                <div class="js-file-preview">
                                    <div class="js-file-image"></div>
                                </div>
                                <% if( /^image/.test(type) ){ %>
                                <div data-fileapi="file.rotate.cw" class="js-file-rotate">
                                    <i class="fa fa-repeat"></i>
                                </div>
                                <% } %>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 0"></div>
                                </div>
                                <div class="js-file-name"><%-name%></div>
                            </div>
                        </div>
                    </div>
                    {/literal}
                    <div class="row">
                        <div class="col-md-24 js-uploader-picker">
                            <div class="small text-muted topic-photo-upload-rules pull-right">
                                {$aLang.uploader_picker}<br>
                                {$aLang.uploader_sort_notice}<br>
                                {$nMaxSixe=Config::Get('module.topic.photoset.photo_max_size')}
                                {$nMaxCount=Config::Get('module.topic.photoset.count_photos_max')}
                                - {$aLang.topic_photoset_upload_rules|ls_lang:"SIZE%%$nMaxSixe":"COUNT%%$nMaxCount"}
                                <div class="js-uploader-picker-supported"></div>
                            </div>
                            <div class="btn btn-success btn-sm js-add js-fileapi-wrapper">
                                <span>{$aLang.topic_photoset_upload_add}</span>
                                <input type="file" name="uploader-upload-image">
                            </div>
                            <div class="js-upload btn btn-success btn-sm">
                                <span>{$aLang.topic_photoset_upload_choose}</span>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="topic_show_photoset" name="topic_show_photoset" value="1"
                       {if $_aRequest.topic_show_photoset==1}checked{/if} />
                {$aLang.topic_show_photoset}
            </label>

            <p class="help-block">
                <small>{$aLang.topic_show_photoset_notice}</small>
            </p>
        </div>
    </div>

</div>

