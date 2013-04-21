{extends file='_index.tpl'}

{block name="content"}

{include file='actions/ActionProfile/profile_top.tpl'}
{include file='menu.talk.tpl'}

<section class="b-widget">
	<header class="b-widget-header">
		{$aLang.talk_blacklist_title}
	</header>


	<div class="b-widget-content">
		<form onsubmit="return ls.talk.addToBlackList();">
			<p><label for="talk_blacklist_add">{$aLang.talk_balcklist_add_label}:</label>
			<input type="text" id="talk_blacklist_add" name="add" class="input-text input-width-full autocomplete-users-sep" /></p>
		</form>


		<div id="black_list_block">
			{if $aUsersBlacklist}
				<ul class="list" id="black_list">
					{foreach from=$aUsersBlacklist item=oUser}
						<li id="blacklist_item_{$oUser->getId()}_area"><a href="{$oUser->getUserWebPath()}" class="user">{$oUser->getLogin()}</a> - <a href="#" id="blacklist_item_{$oUser->getId()}" class="delete">{$aLang.blog_delete}</a></li>
					{/foreach}
				</ul>
			{/if}
		</div>
	</div>
</section>

{/block}