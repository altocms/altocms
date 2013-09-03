{**
 * Загрузка аватара пользователя
 *
 * @styles css/modals.css
 *}

{extends file='modals/_modal_base.tpl'}

{block name='modal_id'}avatar-resize{/block}
{block name='modal_class'}modal-avatar-resize js-modal-default{/block}
{block name='modal_title'}{$aLang.settings_profile_avatar_resize_title}{/block}

{block name='modal_content'}
    <img src="" alt="" class="js-image-crop">
    <br/><br/>
    {$aLang.settings_profile_avatar_resize_text}
{/block}

{block name='modal_footer_begin'}
    <button type="submit" class="button button-primary" onclick="return ls.user.ajaxUploadImageCropSubmit(this);">
        {$aLang.settings_profile_avatar_resize_apply}
    </button>
    <button type="submit" class="button" onclick="return ls.user.ajaxUploadImageCropCancel(this);">
        {$aLang.settings_profile_avatar_resize_cancel}
    </button>
{/block}

{block name='modal_footer_cancel'}{/block}