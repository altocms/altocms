<script type="text/javascript">
    jQuery(function ($) {
        if (window.swfobject.getFlashPlayerVersion()) {
            ls.photoset.initSwfUpload({
                post_params: {
                    'topic_id': {json var=$_aRequest.topic_id}
                }
            });
        }
    });
</script>

<div class="panel panel-default">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a data-toggle="collapse" href="##topic-field-photoset">
                <span class="glyphicon glyphicon-plus-sign"></span>
                {$aLang.topic_toggle_images}
            </a>
        </h5>
    </div>
    <div id="topic-field-photoset" class="panel-collapse collapse {if count($aPhotos)}in{/if}">
        <div class="panel-body topic-photo-upload">
            <div class="small text-muted topic-photo-upload-rules">
                {$nMaxSixe=Config::Get('module.topic.photoset.photo_max_size')}
                {$nMaxCount=Config::Get('module.topic.photoset.count_photos_max')}
                {$aLang.topic_photoset_upload_rules|ls_lang:"SIZE%%$nMaxSixe":"COUNT%%$nMaxCount"}
            </div>

            <input type="hidden" name="topic_main_photo" id="topic_main_photo" value="{$_aRequest.topic_main_photo}"/>

            <ul id="swfu_images" class="small list-unstyled">
                {if count($aPhotos)}
                    {foreach $aPhotos as $oPhoto}
                        {if $_aRequest.topic_main_photo AND $_aRequest.topic_main_photo == $oPhoto->getId()}
                            {$bIsMainPhoto=true}
                        {/if}
                        <li id="photoset_photo_{$oPhoto->getId()}" {if $bIsMainPhoto}class="marked-as-preview"{/if}>
                            <img src="{$oPhoto->getWebPath('100crop')}" alt="image"/>
                            <textarea onblur="ls.photoset.setPreviewDescription('{$oPhoto->getId()}')"
                                      class="form-control">{$oPhoto->getDescription()}</textarea>
                            <br/>
                            <a href="#"
                               onclick="ls.photoset.deletePhoto('{$oPhoto->getId()}'); return false;"
                               class="action-image-delete">{$aLang.topic_photoset_photo_delete}</a>
                            <span id="photo_preview_state_{$oPhoto->getId()}" class="photo-preview-state">
                            {if $bIsMainPhoto}
                                {$aLang.topic_photoset_is_preview}
                            {else}
                                <a href="#" onclick="ls.photoset.setPreview('{$oPhoto->getId()}'); return false;"
                                   class="action-image-setpreview">{$aLang.topic_photoset_mark_as_preview}</a>
                            {/if}
                            </span>
                        </li>
                        {$bIsMainPhoto=false}
                    {/foreach}
                {/if}
                <li id="photoset_photo_ID" style="display: none;">
                    <img src="" alt="image"/>
                    <textarea onblur="ls.photoset.setPreviewDescription('ID')" class="form-control"></textarea>
                    <br/>
                    <a href="#"
                       onclick="ls.photoset.deletePhoto('ID'); return false;"
                       class="action-image-delete">{$aLang.topic_photoset_photo_delete}</a>
                            <span id="photo_preview_state_ID" class="photo-preview-state">
                            <a href="#" onclick="ls.photoset.setPreview('ID'); return false;"
                               class="action-image-setpreview">{$aLang.topic_photoset_mark_as_preview}</a>
                            </span>
                </li>
                <li class="photoset-upload-progress js-photoset-upload-progress" style="display: none;">
                    <div class="progress progress-striped active">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                            0%
                        </div>
                    </div>
                    <span class="photoset-upload-filename js-photoset-upload-filename"></span>
                    <span class="photoset-upload-status js-photoset-upload-status"></span>
                </li>
            </ul>

            <div>
                <span id="photoset-upload-place"></span>
                <a href="#" id="photoset-upload-button"
                   onclick="ls.photoset.showForm(); return false;"
                   class="btn btn-default">
                    {$aLang.topic_photoset_upload_choose}
                </a>
            </div>
        </div>
    </div>
</div>

