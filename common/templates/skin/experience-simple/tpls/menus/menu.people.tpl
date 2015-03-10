 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel-footer">
    <a class="link link-light-gray link-lead link-clear {if $sMenuItemSelect=='all'}active{/if}" href="{router page='people'}">{$aLang.people_menu_users_all}</a>
    <a class="link link-light-gray link-lead link-clear {if $sMenuItemSelect=='online'}active{/if}" href="{router page='people'}online/">{$aLang.people_menu_users_online}</a>
    <a class="link link-light-gray link-lead link-clear {if $sMenuItemSelect=='new'}active{/if}" href="{router page='people'}new/">{$aLang.people_menu_users_new}</a>

    {hook run='menu_people_people_item'}
</div>

{hook run='menu_people'}