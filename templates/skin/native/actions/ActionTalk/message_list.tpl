{**
 * Список сообщений
 *}

<table class="table table-talk nomargin">
	<tbody>
		{foreach $aTalks as $oTalk}
			{$oTalkUserAuthor = $oTalk->getTalkUser()}

			<tr>
				{if $bMessageListCheckboxes}
					<td class="cell-checkbox"><input type="checkbox" name="talk_select[{$oTalk->getId()}]" class="form_talks_checkbox input-checkbox" /></td>
				{/if}
				
				
				<td class="cell-avatar">
					{$oUser = $oTalk->getUser()}
					<img src="{$oUser->getProfileAvatarPath(24)}" alt="avatar" class="avatar" />
				</td>
				<td class="cell-username">
					{strip}
						{assign var="aTalkUserOther" value=[]}
						{foreach from=$oTalk->getTalkUsers() item=oTalkUser name=users}
							{if $oTalkUser->getUserId()!=$oTalk->getUserId()}
								{$aTalkUserOther[]=$oTalkUser}
							{/if}
						{/foreach}
					{/strip}
					<a href="{$oUser->getUserWebPath()}" class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}" {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}title="{$aLang.talk_speaker_not_found}"{/if}>{$oUser->getLogin()}</a>

					<div class="talk-speakers">
						
						<div class="speakers-count">+<i class="icon-native-talk-speakers"></i>{count($aTalkUserOther)}</div>
						
						<div class="speakers-trigger"><i></i></div>
						<div class="speakers">
							{strip}
								
								{foreach from=$aTalkUserOther item=oTalkUser name=users}
									{assign var="oUser" value=$oTalkUser->getUser()}
									{if !$smarty.foreach.users.first}, {/if}<a href="{$oUser->getUserWebPath()}" class="username {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}">{$oUser->getLogin()}</a>
								{/foreach}
							{/strip}
						</div>
					</div>
				</td>
				
				
				<td class="cell-favourite">
					<a href="#" 
					   onclick="return ls.favourite.toggle({$oTalk->getId()},this,'talk');" 
					   class="favourite {if $oTalk->getIsFavourite()}active{/if}" 
					   title="{if $oTalk->getIsFavourite()}{$aLang.talk_favourite_del}{else}{$aLang.talk_favourite_add}{/if}"></a>
				</td>
				<td class="cell-message">
					{strip}
						<a href="{router page='talk'}read/{$oTalk->getId()}/" title="{$oTalk->getTitle()|escape:'html'}">
							{if $oTalkUserAuthor->getCommentCountNew() or ! $oTalkUserAuthor->getDateLast()}
								<strong>{$oTalk->getTitle()|escape:'html'}</strong>
							{else}
								{$oTalk->getTitle()|escape:'html'}
							{/if}
						</a>
						&nbsp;&nbsp;&bull;&nbsp;&nbsp;{$oTalk->getTextLast()|strip_tags|truncate:50:'...'|escape:'html'}
					{/strip}
					
					{if $oUserCurrent->getId()==$oTalk->getUserIdLast()}
						&rarr;
					{else}
						&larr;
					{/if}
				</td>
				<td class="cell-comments">
					{if $oTalk->getCountComment()}
						<span class="block-count">
							{$oTalk->getCountComment()}{if $oTalkUserAuthor->getCommentCountNew()} <strong>+{$oTalkUserAuthor->getCommentCountNew()}</strong>{/if}
						</span>
					{/if}
				</td>
				<td class="cell-date">
					{date_format date=$oTalk->getDate() format="d.m.Y"}<br />
					{date_format date=$oTalk->getDate() format="H:i"}
				</td>
			</tr>
		{/foreach}
		
		
	</tbody>
</table>