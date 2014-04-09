{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_favourite.tpl'}
{/block}

{block name="layout_profile_content"}

    {include file='comments/comment.list.tpl'}

{/block}
