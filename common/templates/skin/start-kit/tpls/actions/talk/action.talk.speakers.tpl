<div class="talk-search talk-recipients">
	<header class="small talk-recipients-header">
		{$aLang.talk_speaker_title}:

		{foreach $oTalk->getTalkUsers() as $oTalkUser}
			{$oUserRecipient=$oTalkUser->getUser()}
			{if $oUser->getId() != $oUserRecipient->getId()}
				<a class="{if $oTalkUser->getUserActive() != $TALK_USER_ACTIVE}inactive{/if}" href="{$oUserRecipient->getProfileUrl()}">{$oUserRecipient->getDisplayName()}</a>{if !$oTalkUser@last}, {/if}
			{/if}
		{/foreach}

		{if $oTalk->getUserId()==E::UserId() OR E::IsAdmin()}
			&nbsp;&nbsp;&nbsp;<a href="#" class="link-dotted" onclick="jQuery('#talk_recipients').toggle(); return false;">{$aLang.talk_speaker_edit}</a>
		{/if}

	</header>

	{if $oTalk->getUserId()==E::UserId() OR E::IsAdmin()}
		<div class="talk-search-content talk-recipients-content" id="talk_recipients">
			<h3>{$aLang.talk_speaker_title}</h3>

			<form onsubmit="return ls.talk.addToTalk({$oTalk->getId()});">
				<div class="form-group">
					<label for="talk_speaker_add">{$aLang.talk_speaker_add_label}</label>
					<input type="text" id="talk_speaker_add" name="add" class="form-control autocomplete-users-sep" />
				</div>

				<input type="hidden" id="talk_id" value="{$oTalk->getId()}" />
			</form>

			<div id="speaker_list_block">
				{if $oTalk->getTalkUsers()}
					<ul class="list-unstyled text-muted" id="speaker_list">
						{foreach $oTalk->getTalkUsers() as $oTalkUser}
							{if $oTalkUser->getUserId()!=E::UserId()}
								{$oUser=$oTalkUser->getUser()}

								{if $oTalkUser->getUserActive()!=$TALK_USER_DELETE_BY_AUTHOR}
									<li id="speaker_item_{$oTalkUser->getUserId()}_area">
										<a class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}" href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
										{if $oTalkUser->getUserActive()==$TALK_USER_ACTIVE}- <a href="#" id="speaker_item_{$oTalkUser->getUserId()}" class="delete">{$aLang.blog_delete}</a>{/if}
									</li>
								{/if}
							{/if}
						{/foreach}
					</ul>
				{/if}
			</div>
		</div>
	{/if}
</div>
