{**
 * Базовый шаблон профиля пользователя
 *}

{extends file='[layouts]layout.base.tpl'}

{block name='layout_content_begin'}
	{**
	 * Шапка профиля
	 *}

	{$oVote = $oUserProfile->getVote()}

	<div class="profile">
		{hook run='profile_top_begin' oUserProfile=$oUserProfile}
		
		<a href="{$oUserProfile->getUserWebPath()}"><img src="{$oUserProfile->getProfileAvatarPath(100)}" alt="avatar" class="avatar" itemprop="photo" /></a>
		
		<ul>
			<li class="profile-name">
				
				{if $oUserProfile->getProfileName()}
					<h2 itemprop="name">{$oUserProfile->getProfileName()|escape:'html'}</h2>
				{/if}
				<p itemprop="nickname" class="{if !$oUserProfile->getProfileName()}no-profile-name{/if}">{$oUserProfile->getLogin()} {if $oUserProfile->isOnline()}<span class="profile-online"></span>{/if}</p>
				
				
			</li>
			<li>
				<div data-vote-type="user"
					 data-vote-id="{$oUserProfile->getId()}"
					 class="vote vote-profile js-vote
						{if $oUserProfile->getRating() >= 0}
							vote-count-positive
						{else}
							vote-count-negative
						{/if} 

						{if $oVote}
							voted 

							{if $oVote->getDirection() > 0}
								voted-up
							{elseif $oVote->getDirection() < 0}
								voted-down
							{/if}
						{/if}">
					<div class="vote-count count js-vote-rating" title="{$aLang.user_vote_count}: {$oUserProfile->getCountVote()}">{$oUserProfile->getRating()}</div>
					<a href="#" class="vote-item vote-down js-vote-down"><i></i></a><a href="#" class="vote-item vote-up js-vote-up"><i></i></a>
				</div>
			</li>
			<li>
				<h4>{$iCountTopicUser}</h4>
				{$iCountTopicUser|declension:$aLang.profile_topics_count_declension:'russian'}
			</li>
			<li>
				<h4>{$iCountCommentUser}</h4>
				{$iCountCommentUser|declension:$aLang.comment_declension:'russian'}
			</li>
		</ul>
		
		{hook run='profile_top_end' oUserProfile=$oUserProfile}
	</div>
	

	<ul class="nav nav-folding">
		{hook run='profile_sidebar_menu_item_first' oUserProfile=$oUserProfile}

		<li {if $sAction=='profile' && ($aParams[0]=='whois' or $aParams[0]=='')}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}">{$aLang.user_menu_profile_whois}</a></li>
		<li {if $sAction=='profile' && $aParams[0]=='wall'}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}wall/">{$aLang.user_menu_profile_wall}{if ($iCountWallUser)>0} <span class="nav-count">{$iCountWallUser}</span>{/if}</a></li>
		<li {if $sAction=='profile' && $aParams[0]=='created'}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}created/topics/">{$aLang.user_menu_publication}{if ($iCountCreated)>0} <span class="nav-count">{$iCountCreated}</span>{/if}</a></li>
		<li {if $sAction=='profile' && $aParams[0]=='favourites'}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}favourites/topics/">{$aLang.user_menu_profile_favourites}{if ($iCountFavourite)>0} <span class="nav-count">{$iCountFavourite}</span>{/if}</a></li>
		<li {if $sAction=='profile' && $aParams[0]=='friends'}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}friends/">{$aLang.user_menu_profile_friends}{if ($iCountFriendsUser)>0} <span class="nav-count">{$iCountFriendsUser}</span>{/if}</a></li> 
		<!-- <li {if $sAction=='profile' && $aParams[0]=='stream'}class="active"{/if}><a href="{$oUserProfile->getUserWebPath()}stream/">{$aLang.user_menu_profile_stream}</a></li>
		
		{if $oUserCurrent and $oUserCurrent->getId() == $oUserProfile->getId()}
			<li {if $sAction=='talk'}class="active"{/if}><a href="{router page='talk'}">{$aLang.talk_menu_inbox}{if $iUserCurrentCountTalkNew} ({$iUserCurrentCountTalkNew}){/if}</a></li>
			<li {if $sAction=='settings'}class="active"{/if}><a href="{router page='settings'}">{$aLang.settings_menu}</a></li>
		{/if} -->
		
		{hook run='profile_sidebar_menu_item_last' oUserProfile=$oUserProfile}
	</ul>
{/block}