 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="row">
    <div class="col-lg-12 user-toggle-publication-block">

        <a href="{$oUserProfile->getProfileUrl()}created/topics/"
           class="btn btn-light-gray {if $sMenuSubItemSelect=='topics'}active{/if}">
            {$aLang.topic_title}  {if $iCountTopicUser} ({$iCountTopicUser}) {/if}
        </a>

        <a class="btn btn-light-gray {if $sMenuSubItemSelect=='comments'}active{/if}"
           href="{$oUserProfile->getProfileUrl()}created/comments/">{$aLang.user_menu_publication_comment}  {if $iCountCommentUser} ({$iCountCommentUser}) {/if}</a>

        {if E::UserId()==$oUserProfile->getId()}
            <a class="btn btn-light-gray {if $sMenuSubItemSelect=='notes'}active{/if}"
               href="{$oUserProfile->getProfileUrl()}created/notes/">{$aLang.user_menu_profile_notes}  {if $iCountNoteUser} ({$iCountNoteUser}) {/if}</a>
        {/if}

        {hook run='menu_profile_created_item' oUserProfile=$oUserProfile}
    </div>
    {hook run='menu_profile_created' oUserProfile=$oUserProfile}
</div>