 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_favourite.tpl'}
{/block}

{block name="layout_profile_content"}

    {include file='topics/topic.list.tpl'}

{/block}
