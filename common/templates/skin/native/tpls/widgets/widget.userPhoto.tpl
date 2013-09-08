{**
 * Блок с фотографией пользователя в профиле
 *
 * @styles css/widgets.css
 *}

{extends file='./_aside.base.tpl'}

{block name='block_type'}profile-photo{/block}

{block name='block_content_after'}
    {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
        <div class="js-ajax-photo-upload">
            <div class="profile-photo-wrapper">
                <img src="{$oUserProfile->getPhotoUrl(250)}" alt="photo" class="profile-photo js-ajax-image-upload-image" />
            </div>
            <ul class="profile-photo-menu">
                <li class="profile-photo-edit">
                    <label for="profile-photo" class="form-input-file">
                        <span class="link-dotted js-ajax-image-upload-choose">
                            {if $oUserCurrent->getProfilePhoto()}
                                {$aLang.settings_profile_photo_change}
                            {else}
                                {$aLang.settings_profile_photo_upload}
                            {/if}
                        </span>
                        <input type="file" name="photo" id="profile-photo" class="js-ajax-image-upload-file"
                               data-resize-form="#photo-resize">
                    </label>
                </li>
                <li class="profile-photo-remove">
                    <a href="#" class="link-dotted js-ajax-image-upload-remove"
                       style="{if !$oUserCurrent->getProfilePhoto()}display:none;{/if}">
                        {$aLang.settings_profile_photo_delete}
                    </a>
                </li>
            </ul>
        </div>
    {else}
        <div class="profile-photo-wrapper">
            <div class="status {if $oUserProfile->isOnline()}status-online{else}status-offline{/if}">
                {if $oUserProfile->isOnline()}
                    {$aLang.user_status_online}
                {else}
                    {$aLang.user_status_offline}
                {/if}
            </div>
            <img src="{$oUserProfile->getPhotoUrl()}" alt="photo" class="profile-photo" />
        </div>
    {/if}
{/block}
