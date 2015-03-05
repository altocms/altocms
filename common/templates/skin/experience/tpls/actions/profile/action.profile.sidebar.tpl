 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{hook run='profile_sidebar_begin' oUserProfile=$oUserProfile}

<div class="panel panel-default sidebar user-panel raised">
    <div class="panel-body">
        <div class="panel-content">

            {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}
            <div class ="{if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}js-alto-uploader{/if} settings-photo-change"
                    {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                        data-target        ="profile_photo"
                        data-target-id     ="{E::User()->getId()}"
                        data-title         ="{$aLang.settings_profile_photo_resize_title}"
                        data-help          ="{$aLang.settings_profile_photo_resize_text}"
                        data-aspect-ratio  = "{E::ModuleUploader()->GetConfigAspectRatio('*', 'profile_photo')}"
                        data-empty         ="{E::User()->getDefaultPhotoUrl('222crop')}"
                        data-preview-crop  ="222crop"
                        data-crop          ="yes"
                    {/if}
                    >

                <img style="width: 100%; display: block; margin-bottom: 8px;"
                     src="{$oUserProfile->getPhotoUrl('222crop')}"
                     id="profile-photo-image"
                     class="profile-photo js-uploader-image"/>
                <span class="label label-{if $oUserProfile->isOnline()}success{else}danger{/if}">
                    {if $oUserProfile->isOnline()}{$aLang.user_status_online}{else}{$aLang.user_status_offline}{/if}
                </span>

                {* Меню управления картинкой фона блога *}
                {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                    <div class="uploader-actions profile-photo-menu">

                        {* Кнопка загрузки картинки *}
                        <a href="#" onclick="return false;" class="link link-lead link-blue link-clear mat8 js-uploader-button-upload"
                           data-toggle="file" data-target="#profile-photo-file">
                            <i class="fa fa-pencil"></i>&nbsp;{$aLang.settings_profile_photo_change}
                        </a>

                        {* Кнопка удаления картинки *}
                        <br/>
                        <a href="#" class="link link-lead link-red-blue link-clear js-uploader-button-remove"
                           {if !$oUserCurrent->hasPhoto()}style="display: none;"{/if}>
                            <i class="fa fa-times"></i>&nbsp;{$aLang.settings_profile_photo_delete}
                        </a>

                        {* Файл для загрузки *}
                        <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

                    </div>

                    {* Форма обрезки картинки при ее загрузке *}
                    {include_once file="modals/modal.crop_img.tpl"}
                {/if}
            </div>


            {*{if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}*}
                {*<div class="profile-photo-menu">*}
                    {*<br/>*}
                    {*<a class="link link-lead link-blue link-clear mat8" href="#" onclick="return false;" data-toggle="file" data-target="#profile-photo-file"><i class="fa fa-pencil"></i>&nbsp;*}
                        {*{if $oUserCurrent->getProfilePhoto()}*}
                            {*{$aLang.settings_profile_photo_change}*}
                        {*{else}*}
                            {*{$aLang.settings_profile_photo_upload}*}
                        {*{/if}*}
                    {*</a>*}
                    {*<br/>*}
                    {*<a href="#" class="link link-lead link-red-blue link-clear js-profile-photo-remove" {if !$oUserCurrent->getProfilePhoto()}style="visibility: hidden;"{/if}>*}
                        {*<i class="fa fa-times"></i>&nbsp;{$aLang.settings_profile_photo_delete}*}
                    {*</a>*}
                    {*<input type="file" name="photo" id="profile-photo-file" class="js-profile-photo-file"*}
                           {*data-target=".js-profile-photo-image">*}
                {*</div>*}
                {*{include_once file="modals/modal.crop_img.tpl"}*}
            {*{/if}*}

            {hook run='profile_sidebar_menu_before' oUserProfile=$oUserProfile}

            <ul class="nav nav-pills nav-stacked user-nav">
                {hook run='profile_sidebar_menu_item_first' oUserProfile=$oUserProfile}
                <li {if $sAction=='profile' AND ($aParams[0]=='whois' OR $aParams[0]=='')}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}"><i class="fa fa-info-circle"></i>{$aLang.user_menu_profile_whois}</a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='wall'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}wall/"><i class="fa fa-edit"></i>{$aLang.user_menu_profile_wall}{if ($iCountWallUser)>0}<span class="badge pull-right">{$iCountWallUser}</span>{/if}</a></li>
                <li {if $sAction=='profile' AND $aParams[0]=='created'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}created/topics/">
                        <i class="fa fa-file-o"></i>{$aLang.user_menu_publication}{if ($iCountCreated)>0} <span class="badge pull-right">{$iCountCreated}</span>{/if}</a></li>
                <li {if $sAction=='profile' AND $aParams[0]=='favourites'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}favourites/topics/"><i class="fa fa-star-o"></i>{$aLang.user_menu_profile_favourites}{if ($iCountFavourite)>0}<span class="badge pull-right">{$iCountFavourite}</span>{/if}</a></li>
                <li {if $sAction=='profile' AND $aParams[0]=='stream'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}stream/"><i class="fa fa-bar-chart-o"></i>{$aLang.user_menu_profile_stream}</a></li>
                <li {if $sAction=='profile' AND $aParams[0]=='friends'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}friends/"><i class="fa fa-users"></i>{$aLang.user_menu_profile_friends}{if ($iCountFriendsUser)>0} <span class="badge pull-right">{$iCountFriendsUser}</span>{/if}</a></li>
                {if E::UserId() == $oUserProfile->getId()}
                <li {if $sAction=='talk'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{router page='talk'}"><i class="fa fa-envelope-o"></i>{$aLang.talk_menu_inbox}{if $iUserCurrentCountTalkNew}<span class="badge pull-right"> {$iUserCurrentCountTalkNew}</span>{/if}</a></li>
                <li {if $sAction=='settings'}class="active"{/if}><a class="link link-lead link-dual link-clear" href="{router page='settings'}"><i class="fa fa-cogs"></i>{$aLang.settings_menu}</a></li>
                {/if}
                {hook run='profile_sidebar_menu_item_last' oUserProfile=$oUserProfile}
            </ul>
        </div>
    </div>
    <div class="panel-footer">
        <a href="#" class="link link-dual link-lead link-clear">
            <i class="fa fa-rss"></i>RSS
        </a>
    </div>
</div>

{if E::UserId()==$oUserProfile->getId() && Router::GetParam(0) == 'favourites'}
    {widget name="TagsFavouriteTopic" user=$oUserProfile}
{/if}


{if E::IsUser() AND E::UserId() != $oUserProfile->getId()}
<div class="panel panel-default sidebar user-panel raised widget">

        <div class="panel-body pab24 js-usernote" data-user-id="{$oUserProfile->getId()}">

            <div class="profile-note js-usernote-wrap" {if !$oUserNote}style="display: none;"{/if}>
                <div class="js-usernote-text pab24">
                    {if $oUserNote}
                        {$oUserNote->getText()|nl2br}
                    {/if}
                </div>
                <a href="#" class="btn btn-blue corner-no pull-right js-usernote-button-edit">{$aLang.user_note_form_edit}</a>
                <a href="#" class="btn btn-light corner-no  js-usernote-button-remove">{$aLang.user_note_form_delete}</a>
            </div>

            <div class="js-usernote-form" style="display: none;">
                <div class="form-group">
                    <textarea rows="4" cols="20" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-light corner-no js-usernote-form-cancel">{$aLang.user_note_form_cancel}</button>
                <button type="submit" class="btn btn-blue corner-no pull-right js-usernote-form-save">{$aLang.user_note_form_save}</button>
            </div>

            <a href="#" class="link-dotted js-usernote-button-add"
               {if $oUserNote}style="display:none;"{/if}>{$aLang.user_note_add}</a>

        </div>


</div>
{/if}

{hook run='profile_sidebar_end' oUserProfile=$oUserProfile}
