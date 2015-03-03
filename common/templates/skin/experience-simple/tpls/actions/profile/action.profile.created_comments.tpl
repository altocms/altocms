 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_created.tpl'}
{/block}

{block name="layout_profile_content"}

    {include file='comments/comment.list.tpl'}

{/block}
