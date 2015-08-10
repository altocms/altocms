 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="row">
    <div class="col-lg-24 user-toggle-publication-block">

        <a href="{$oUserProfile->getProfileUrl()}created/topics/" class="btn btn-light-gray {if $sMenuSubItemSelect=='topics'}active{/if}">
            {$aLang.topic_title}
            {if $aProfileStats['count_topics']} ({$aProfileStats['count_topics']}) {/if}
        </a>

        <a href="{$oUserProfile->getProfileUrl()}created/photos/" class="btn btn-light-gray {if $sMenuSubItemSelect=='photos'}active{/if}">
            {$aLang.user_menu_publication_photos}
            {if $aProfileStats['count_images']} ({$aProfileStats['count_images']}) {/if}
        </a>

        <a href="{$oUserProfile->getProfileUrl()}created/comments/" class="btn btn-light-gray {if $sMenuSubItemSelect=='comments'}active{/if}">
            {$aLang.user_menu_publication_comment}
            {if $aProfileStats['count_comments']} ({$aProfileStats['count_comments']}) {/if}
        </a>

        {if E::UserId()==$oUserProfile->getId()}
            <a href="{$oUserProfile->getProfileUrl()}created/notes/" class="btn btn-light-gray {if $sMenuSubItemSelect=='notes'}active{/if}">
                {$aLang.user_menu_profile_notes}
                {if $aProfileStats['count_usernotes']} ({$aProfileStats['count_usernotes']}) {/if}
            </a>
        {/if}

        {hook run='menu_profile_created_item' oUserProfile=$oUserProfile}
    </div>
    {hook run='menu_profile_created' oUserProfile=$oUserProfile}
</div>