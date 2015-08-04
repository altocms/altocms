 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="row profile-header-submenu">
    <div class="col-lg-24 mab12">

        <a class="btn btn-default {if $sMenuSubItemSelect=='topics'}active{/if}"
           href="{$oUserProfile->getProfileUrl()}favourites/topics/">{$aLang.user_menu_profile_favourites_topics}  {if $iCountTopicFavourite} ({$iCountTopicFavourite}) {/if}</a>

        <a class="btn btn-default {if $sMenuSubItemSelect=='comments'}active{/if}"
           href="{$oUserProfile->getProfileUrl()}favourites/comments/">{$aLang.user_menu_profile_favourites_comments}  {if $iCountCommentFavourite} ({$iCountCommentFavourite}) {/if}</a>


        {hook run='menu_profile_favourite_item' oUserProfile=$oUserProfile}


    </div>

    {hook run='menu_profile_favourite' oUserProfile=$oUserProfile}

</div>