<h3 class="profile-page-header">{$aLang.user_menu_publication}</h3>

<ul class="b-nav-pills nav-pills-profile">
	<li {if $sMenuSubItemSelect=='topics'}class="active"{/if}>
		<a href="{$oUserProfile->getUserWebPath()}created/topics/">{$aLang.topic_title}  {if $iCountTopicUser} ({$iCountTopicUser}) {/if}</a>
	</li>
	
	<li {if $sMenuSubItemSelect=='comments'}class="active"{/if}>
		<a href="{$oUserProfile->getUserWebPath()}created/comments/">{$aLang.user_menu_publication_comment}  {if $iCountCommentUser} ({$iCountCommentUser}) {/if}</a>
	</li>
	
	{if $oUserCurrent and $oUserCurrent->getId()==$oUserProfile->getId()}
		<li {if $sMenuSubItemSelect=='notes'}class="active"{/if}>
			<a href="{$oUserProfile->getUserWebPath()}created/notes/">{$aLang.user_menu_profile_notes}  {if $iCountNoteUser} ({$iCountNoteUser}) {/if}</a>
		</li>
	{/if}
	
	{hook run='menu_profile_created_item' oUserProfile=$oUserProfile}
</ul>

{*if $sMenuSubItemSelect=='topics'}
	<ul class="b-nav-pills nav-pills-profile">
		<li {if $sTopicsTypeSelect=='all'}class="active"{/if}>
			<a href="{$oUserProfile->getUserWebPath()}created/topics/all/">{$aLang.topic_created_all}  {if $iCountTopicUser} ({$iCountTopicUser}) {/if}</a>
		</li>

		<li {if $sTopicsTypeSelect=='collective'}class="active"{/if}>
			<a href="{$oUserProfile->getUserWebPath()}created/topics/collective/">{$aLang.topic_created_collective}  {if $iCountTopicCollectiveUser} ({$iCountTopicCollectiveUser}) {/if}</a>
		</li>

		<li {if $sTopicsTypeSelect=='personal'}class="active"{/if}>
			<a href="{$oUserProfile->getUserWebPath()}created/topics/personal/">{$aLang.topic_created_personal}  {if $iCountTopicPersonalUser} ({$iCountTopicPersonalUser}) {/if}</a>
		</li>
	</ul>
{/if*}

{hook run='menu_profile_created' oUserProfile=$oUserProfile}
