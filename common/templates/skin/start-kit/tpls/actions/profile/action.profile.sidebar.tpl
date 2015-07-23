{hook run='profile_sidebar_begin' oUserProfile=$oUserProfile}

<section class="panel panel-default widget widget-type-profile">
    <div class="panel-body">

        {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}
        <div class ="{if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}js-alto-uploader{/if} settings-photo-change"
             {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                 data-target        ="profile_photo"
                 data-target-id     ="{E::User()->getId()}"
                 data-title         ="{$aLang.settings_profile_photo_resize_title}"
                 data-help          ="{$aLang.settings_profile_photo_resize_text}"
                 data-aspect-ratio  ="{E::ModuleUploader()->GetConfigAspectRatio('*', 'profile_photo')}"
                 data-empty         ="{E::User()->getDefaultPhotoUrl('250crop')}"
                 data-preview-crop  ="250crop"
                 data-crop          ="yes"
             {/if}
            >

            <div class="profile-photo-wrapper">
                <span class="label {if $oUserProfile->isOnline()}label-success{else}label-danger{/if} status">
                    {if $oUserProfile->isOnline()}{$aLang.user_status_online}{else}{$aLang.user_status_offline}{/if}
                </span>
                {* Картинка фона блога *}
                <img style="width: 100%; display: block; margin-bottom: 8px;"
                     src="{$oUserProfile->getPhotoUrl('250crop')}"
                     id="profile-photo-image"
                     class="profile-photo js-uploader-image"/>
            </div>

            {* Меню управления картинкой фона блога *}
            {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                <div class="uploader-actions profile-photo-menu">

                    {* Кнопка загрузки картинки *}
                    <a href="#" onclick="return false;" class="btn btn-default btn-xs js-uploader-button-upload"
                       data-toggle="file" data-target="#profile-photo-file">
                        {$aLang.settings_profile_photo_change}
                    </a>

                    {* Кнопка удаления картинки *}
                    <br/>
                    <a href="#" class="link-dotted js-uploader-button-remove"
                       {if !$oUserCurrent->hasPhoto()}style="display: none;"{/if}>
                        {$aLang.settings_profile_photo_delete}
                    </a>

                    {* Файл для загрузки *}
                    <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

                </div>

                {* Форма обрезки картинки при ее загрузке *}
                {include_once file="modals/modal.crop_img.tpl"}
            {/if}
        </div>

    </div>
</section>

{hook run='profile_sidebar_menu_before' oUserProfile=$oUserProfile}

<section class="panel panel-default widget widget-type-profile-nav">
    <div class="panel-body">

        <ul class="nav nav-pills nav-stacked">
            {hook run='profile_sidebar_menu_item_first' oUserProfile=$oUserProfile}
            <li {if $sAction=='profile' AND ($aParams[0]=='whois' OR $aParams[0]=='')}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}">{$aLang.user_menu_profile_whois}</a></li>
            <li {if $sAction=='profile' AND $aParams[0]=='wall'}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}wall/">{$aLang.user_menu_profile_wall}{if ($iCountWallUser)>0}
                        <span class="badge pull-right">{$iCountWallUser}</span>{/if}</a></li>
            <li {if $sAction=='profile' AND $aParams[0]=='created'}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}created/topics/">{$aLang.user_menu_publication}{if ($iCountCreated)>0}
                        <span class="badge pull-right">{$iCountCreated}</span>{/if}</a></li>
            <li {if $sAction=='profile' AND $aParams[0]=='favourites'}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}favourites/topics/">{$aLang.user_menu_profile_favourites}{if ($iCountFavourite)>0}
                        <span class="badge pull-right">{$iCountFavourite}</span>{/if}</a></li>
            <li {if $sAction=='profile' AND $aParams[0]=='friends'}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}friends/">{$aLang.user_menu_profile_friends}{if ($iCountFriendsUser)>0}
                        <span class="badge pull-right">{$iCountFriendsUser}</span>{/if}</a></li>
            <li {if $sAction=='profile' AND $aParams[0]=='stream'}class="active"{/if}><a
                        href="{$oUserProfile->getProfileUrl()}stream/">{$aLang.user_menu_profile_stream}</a></li>

            {if E::UserId() == $oUserProfile->getId()}
                <li {if $sAction=='talk'}class="active"{/if}><a
                            href="{router page='talk'}">{$aLang.talk_menu_inbox}{if $iUserCurrentCountTalkNew}<span
                                class="badge pull-right">{$iUserCurrentCountTalkNew}</span>{/if}</a></li>
                <li {if $sAction=='settings'}class="active"{/if}><a
                            href="{router page='settings'}">{$aLang.settings_menu}</a></li>
            {/if}
            {hook run='profile_sidebar_menu_item_last' oUserProfile=$oUserProfile}
        </ul>

    </div>
</section>

{if E::User() AND E::UserId()!=$oUserProfile->getId()}
    <script type="text/javascript">
        jQuery(function ($) {
            ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
        });
    </script>
    <section class="panel panel-default widget widget-type-profile-actions">
        <div class="panel-body">

            <div class="widget-content">
                <ul class="list-unstyled profile-actions" id="profile_actions">
                    {include file='actions/profile/action.profile.friend_item.tpl' oUserFriend=$oUserProfile->getUserFriend()}
                    <li>
                        <a href="{router page='talk'}add/?talk_users={$oUserProfile->getLogin()}">{$aLang.user_write_prvmsg}</a>
                    </li>
                    <li>
                        <a href="#" onclick="ls.user.followToggle(this, {$oUserProfile->getId()}); return false;"
                           class="{if $oUserProfile->isFollow()}followed{/if}">
                            {if $oUserProfile->isFollow()}{$aLang.profile_user_unfollow}{else}{$aLang.profile_user_follow}{/if}
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </section>
{/if}

{if E::IsUser() AND E::UserId() != $oUserProfile->getId()}
    <section class="panel panel-default widget widget-type-profile-note">
        <div class="panel-body js-usernote" data-user-id="{$oUserProfile->getId()}">

            <div class="profile-note js-usernote-wrap" {if !$oUserNote}style="display: none;"{/if}>
                <div class="usernote-header">
                    <ul class="list-unstyled list-inline small pull-right actions js-usernote-actions">
                        <li><span class="glyphicon glyphicon-cog actions-tool"></span></li>
                        <li>
                            <a href="#" class="js-usernote-button-edit">{$aLang.user_note_form_edit}</a>
                        </li>
                        <li>
                            <a href="#" class="js-usernote-button-remove">{$aLang.user_note_form_delete}</a>
                        </li>
                    </ul>
                </div>
                <div class="js-usernote-text">
                    {if $oUserNote}
                        {$oUserNote->getText()|nl2br}
                    {/if}
                </div>
            </div>

            <div class="js-usernote-form" style="display: none;">
                <div class="form-group">
                    <textarea rows="4" cols="20" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-default js-usernote-form-cancel">{$aLang.user_note_form_cancel}</button>
                <button type="submit" class="btn btn-success js-usernote-form-save">{$aLang.user_note_form_save}</button>
            </div>

            <a href="#" class="link-dotted js-usernote-button-add"
               {if $oUserNote}style="display:none;"{/if}>{$aLang.user_note_add}</a>

        </div>
    </section>
{/if}

{hook run='profile_sidebar_end' oUserProfile=$oUserProfile}
