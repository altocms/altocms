{**
 * Список всех пользователей
 *}

{extends file='[layouts]layout.base.tpl'}

{block name='layout_options'}
	{$sNav = 'users'}
{/block}

{block name='layout_content'}
	<div id="users-list-original">
		{router page='people' assign=sUsersRootPage}
		{include file='user_list.tpl' aUsersList=$aUsersRating bUsersUseOrder=true sUsersRootPage=$sUsersRootPage}
	</div>
{/block}