{if $aUsersList}
	<ul class="user-list-avatar">
		{foreach from=$aUsersList item=oUserList}
			{$oSession=$oUserList->getSession()}

			<li>
				<a href="{$oUserList->getProfileUrl()}" title="{$oUserList->getLogin()}"><img src="{$oUserList->getAvatarUrl(48)}" alt="avatar" class="avatar" /></a>
			</li>
		{/foreach}
	</ul>
{else}
	{if $sUserListEmpty}
		<div class="notice-empty">{$sUserListEmpty}</div>
	{else}
		<div class="notice-empty">{$aLang.user_empty}</div>
	{/if}
{/if}

{include file='paging.tpl' aPaging=$aPaging}