<ul class="nav nav-pills">
	<li {if $sMenuItemSelect=='all'}class="active"{/if}><a href="{R::GetLink("people")}">{$aLang.people_menu_users_all}</a></li>
	<li {if $sMenuItemSelect=='online'}class="active"{/if}><a href="{R::GetLink("people")}online/">{$aLang.people_menu_users_online}</a></li>
	<li {if $sMenuItemSelect=='new'}class="active"{/if}><a href="{R::GetLink("people")}new/">{$aLang.people_menu_users_new}</a></li>
	
	{hook run='menu_people_people_item'}
</ul>

{hook run='menu_people'}
