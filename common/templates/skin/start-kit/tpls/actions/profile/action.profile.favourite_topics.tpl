{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_favourite.tpl'}
{/block}

{block name="layout_profile_content"}

    {if E::UserId()==$oUserProfile->getId()}
        {widget name="TagsFavouriteTopic" user=$oUserProfile}
    {/if}

    {include file='topics/topic.list.tpl'}

{/block}
