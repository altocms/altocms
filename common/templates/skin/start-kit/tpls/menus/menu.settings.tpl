<div class="page-header">
	<h1>{$aLang.settings_menu}</h1>
</div>


<div class="row nav-filter-wrapper">
	<div class="col-lg-12">

		<ul class="nav nav-pills">
			<li {if $sMenuSubItemSelect=='profile'}class="active"{/if}><a href="{router page='settings'}profile/">{$aLang.settings_menu_profile}</a></li>
			<li {if $sMenuSubItemSelect=='account'}class="active"{/if}><a href="{router page='settings'}account/">{$aLang.settings_menu_account}</a></li>
			<li {if $sMenuSubItemSelect=='tuning'}class="active"{/if}><a href="{router page='settings'}tuning/">{$aLang.settings_menu_tuning}</a></li>
	
			{if Config::Get('general.reg.invite')}
				<li {if $sMenuItemSelect=='invite'}class="active"{/if}>
					<a href="{router page='settings'}invite/">{$aLang.settings_menu_invite}</a>
				</li>
			{/if}

			{hook run='menu_settings_settings_item'}
		</ul>

		{hook run='menu_settings'}
		
	</div>
</div>
