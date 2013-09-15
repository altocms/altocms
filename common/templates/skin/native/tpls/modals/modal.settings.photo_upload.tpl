{**
 * Загрузка фото пользователя
 *
 * @styles css/modals.css
 *}

{extends file='modals/_modal_base.tpl'}

{block name='modal_id'}photo-resize{/block}
{block name='modal_class'}modal-photo-resize js-modal-default{/block}
{block name='modal_title'}{$aLang.uploadimg}{/block}

{block name='modal_content'}
    <img src="" alt="" class="js-image-crop">
{/block}

{block name='modal_footer_begin'}
    <button type="submit" class="btn-primary"
            onclick="return ls.user.ajaxUploadImageCropSubmit(this);">
        {$aLang.settings_profile_avatar_resize_apply}
    </button>
    <button type="submit" class="btn" onclick="return ls.user.ajaxUploadImageCropCancel(this);">
        {$aLang.settings_profile_avatar_resize_cancel}
    </button>
{/block}

{block name='modal_footer_cancel'}{/block}
