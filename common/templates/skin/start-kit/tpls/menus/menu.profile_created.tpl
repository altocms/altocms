<div class="row profile-header-submenu">
    <div class="col-lg-12">

        <ul class="nav nav-pills">
            <li {if $sMenuSubItemSelect=='topics'}class="active"{/if}>
                <a href="{$oUserProfile->getProfileUrl()}created/topics/">{$aLang.topic_title}  {if $iCountTopicUser} ({$iCountTopicUser}) {/if}</a>
            </li>


            <li class="{if $sMenuSubItemSelect=='photos'}active{/if}">
                <a href="{$oUserProfile->getProfileUrl()}created/photos/">{$aLang.user_menu_publication_photos}  {if $iPhotoCount} ({$iPhotoCount}) {/if}</a>
            </li>


            <li {if $sMenuSubItemSelect=='comments'}class="active"{/if}>
                <a href="{$oUserProfile->getProfileUrl()}created/comments/">{$aLang.user_menu_publication_comment}  {if $iCountCommentUser} ({$iCountCommentUser}) {/if}</a>
            </li>

            {if E::UserId()==$oUserProfile->getId()}
                <li {if $sMenuSubItemSelect=='notes'}class="active"{/if}>
                    <a href="{$oUserProfile->getProfileUrl()}created/notes/">{$aLang.user_menu_profile_notes}  {if $iCountNoteUser} ({$iCountNoteUser}) {/if}</a>
                </li>
            {/if}

            {hook run='menu_profile_created_item' oUserProfile=$oUserProfile}
        </ul>

        {hook run='menu_profile_created' oUserProfile=$oUserProfile}

    </div>
</div>
