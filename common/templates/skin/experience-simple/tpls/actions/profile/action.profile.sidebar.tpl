 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{hook run='profile_sidebar_begin' oUserProfile=$oUserProfile}

<div class="panel panel-default sidebar user-panel flat">
    <div class="panel-body">
        <div class="panel-content">

            {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}
            <div class ="{if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}js-alto-uploader{/if} settings-photo-change"
                    {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                        data-target        ="profile_photo"
                        data-target-id     ="{E::User()->getId()}"
                        data-title         ="{$aLang.settings_profile_photo_resize_title}"
                        data-help          ="{$aLang.settings_profile_photo_resize_text}"
                        data-aspect-ratio  ="{E::ModuleUploader()->GetConfigAspectRatio('*', 'profile_photo')}"
                        data-empty         ="{E::User()->getDefaultPhotoUrl('240crop')}"
                        data-preview-crop  ="240crop"
                        data-crop          ="yes"
                    {/if}
                    >

                <div class="profile-logo-container">
                    <img src="{$oUserProfile->getPhotoUrl('240crop')}"
                         id="profile-photo-image"
                         class="profile-photo js-uploader-image"/>
                    <span class="profile-online-status {if $oUserProfile->isOnline()}success{else}danger{/if}"></span>
                </div>

                {* Меню управления картинкой фона блога *}
                {if $sAction=='settings' AND E::UserId() == $oUserProfile->getId()}
                    <div class="uploader-actions profile-photo-menu">

                        {* Кнопка загрузки картинки *}
                        <a href="#" onclick="return false;" class="link link-lead link-blue link-clear mat8 js-uploader-button-upload"
                           data-toggle="file" data-target="#profile-photo-file">
                            {$aLang.settings_profile_photo_change}
                        </a>

                        {* Кнопка удаления картинки *}
                        {* Файл для загрузки *}
                        <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

                    </div>
                    <div class="uploader-actions profile-photo-menu">
                        <a href="#" class="link link-lead link-red-blue link-clear js-uploader-button-remove"
                           {if !$oUserCurrent->hasPhoto()}style="display: none;"{/if}>
                            {$aLang.settings_profile_photo_delete}
                        </a>
                    </div>

                    {* Форма обрезки картинки при ее загрузке *}
                    {include_once file="modals/modal.crop_img.tpl"}
                {/if}
            </div>

            {if $oUserProfile}
                {$oSession=$oUserProfile->getSession()}
                {if $oSession}
                    <div class="time-info">
                        <span>{$aLang.profile_date_last}</span>
                        <span class="pull-right">{$oSession->getDateLast()|date_format:"d.m.Y H:i"}</span>
                    </div>
                {/if}
            {/if}

            {hook run='profile_sidebar_menu_before' oUserProfile=$oUserProfile}

            <ul class="nav nav-pills nav-stacked user-nav">
                {hook run='profile_sidebar_menu_item_first' oUserProfile=$oUserProfile}
                <li {if $sAction=='profile' AND ($aParams[0]=='whois' OR $aParams[0]=='')}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}">
                        <i class="fa fa-info-circle"></i>{$aLang.user_menu_profile_whois}</a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='wall'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}wall/">
                        <i class="fa fa-edit"></i>{$aLang.user_menu_profile_wall}
                        {if ($aProfileStats['count_wallrecords'])>0}
                            <span class="badge pull-right">{$aProfileStats['count_wallrecords']}</span>
                        {/if}
                    </a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='created'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}created/topics/">
                        <i class="fa fa-file-o"></i>{$aLang.user_menu_publication}
                        {if ($aProfileStats['count_created'])>0}
                            <span class="badge pull-right">{$aProfileStats['count_created']}</span>
                        {/if}
                    </a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='favourites'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}favourites/topics/">
                        <i class="fa fa-star-o"></i>{$aLang.user_menu_profile_favourites}
                        {if ($aProfileStats['count_favourites'])>0}
                            <span class="badge pull-right">{$aProfileStats['count_favourites']}</span>
                        {/if}
                    </a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='stream'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}stream/">
                        <i class="fa fa-bar-chart-o"></i>{$aLang.user_menu_profile_stream}
                    </a>
                </li>
                <li {if $sAction=='profile' AND $aParams[0]=='friends'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{$oUserProfile->getProfileUrl()}friends/">
                        <i class="fa fa-users"></i>{$aLang.user_menu_profile_friends}
                        {if ($aProfileStats['count_friends'])>0}
                            <span class="badge pull-right">{$aProfileStats['count_friends']}</span>
                        {/if}
                    </a>
                </li>

                {if E::UserId() == $oUserProfile->getId()}
                <li {if $sAction=='talk'}class="active"{/if}>
                    {if $iUserCurrentCountTalkNew}
                    <a class="link link-lead link-dual link-clear" href="{router page='talk'}">
                        <i class="fa fa-envelope"></i>{$aLang.talk_menu_inbox}
                        <span class="badge pull-right"> {$iUserCurrentCountTalkNew}</span>
                    </a>
                    {else}
                    <a class="link link-lead link-dual link-clear" href="{router page='talk'}">
                        <i class="fa fa-envelope-o"></i>{$aLang.talk_menu_inbox}
                    </a>
                    {/if}
                </li>
                <li {if $sAction=='settings'}class="active"{/if}>
                    <a class="link link-lead link-dual link-clear" href="{router page='settings'}">
                        <i class="fa fa-cogs"></i>{$aLang.settings_menu}
                    </a>
                </li>
                {/if}

                {hook run='profile_sidebar_menu_item_last' oUserProfile=$oUserProfile}
            </ul>
        </div>
    </div>
    <div class="panel-footer">
        <a href="{router page='rss'}personal_blog/{$oUserProfile->getLogin()}/" class="link link-dual link-lead link-clear">
            <i class="fa fa-rss"></i>RSS
        </a>
    </div>
</div>

{if E::UserId()==$oUserProfile->getId() && Router::GetParam(0) == 'favourites'}
    {widget name="TagsFavouriteTopic" user=$oUserProfile}
{/if}

{if E::IsUser() AND E::UserId() != $oUserProfile->getId()}
<div class="panel panel-default sidebar user-panel flat widget">

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

            <a href="#" class="link-dotted js-usernote-button-add" {if $oUserNote}style="display:none;"{/if}>
                {$aLang.user_note_add}
            </a>

        </div>
</div>
{/if}

{hook run='profile_sidebar_end' oUserProfile=$oUserProfile}
