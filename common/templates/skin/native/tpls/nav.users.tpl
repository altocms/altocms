{**
 * Навигация по пользователям
 *}

<ul class="nav nav-userlist nav-userlist-switcher">
    <li><a href="?view=1" title="{$aLang.grid}"><i class="icon-native-userlist-grid {if $smarty.cookies.view=='1'}active{/if}"></i></a></li>
    <li><a href="?view=2" title="{$aLang.list}"><i class="icon-native-userlist-list {if $smarty.cookies.view=='2'}active{/if}"></i></a></li>
</ul>

<ul class="nav nav-pills nav-userlist">
	<li {if $sMenuItemSelect=='all'}class="active"{/if}><a href="{router page='people'}">{$aLang.people_menu_users_all}</a></li>
	<li {if $sMenuItemSelect=='online'}class="active"{/if}><a href="{router page='people'}online/">{$aLang.people_menu_users_online}</a></li>
	<li {if $sMenuItemSelect=='new'}class="active"{/if}><a href="{router page='people'}new/">{$aLang.people_menu_users_new}</a></li>
	
	{hook run='menu_people_people_item'}
</ul>

{hook run='menu_people'}