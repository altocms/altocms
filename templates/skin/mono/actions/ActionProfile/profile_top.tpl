<div class="b-profile-top">
	{hook run='profile_top_begin' oUserProfile=$oUserProfile}

    {if $oUserProfile->GetId() == E::UserId() AND $sAction=='settings'}
        <div class="b-profile-avatar">
            <img src="{$oUserCurrent->getProfileAvatarPath(100)}" id="avatar-img" />

            <div class="b-profile-avatar-actions">
                <a href="#" id="avatar-upload" class="link-dotted">
                    {if $oUserCurrent->getProfileAvatar()}
                    {$aLang.settings_profile_avatar_change}
                {else}
                    {$aLang.settings_profile_avatar_upload}
                {/if}
                </a><br />
                <a href="#" id="avatar-remove" class="link-dotted" onclick="return ls.user.removeAvatar();" style="{if !$oUserCurrent->getProfileAvatar()}display:none;{/if}">{$aLang.settings_profile_avatar_delete}</a>
            </div>

            <div id="avatar-resize" class="b-modal">
                <header class="b-modal-header">
                    <h3>{$aLang.uploadimg}</h3>
                </header>

                <div class="b-modal-content">
                    <p><img src="" alt="" id="avatar-resize-original-img"></p>
                </div>
                <div class="b-modal-footer">
                    <button type="submit" class="btn" onclick="return ls.user.cancelAvatar();">{$aLang.settings_profile_avatar_resize_cancel}</button>
                    <button type="submit" class="btn-primary" onclick="return ls.user.resizeAvatar();">{$aLang.settings_profile_avatar_resize_apply}</button>
                </div>
            </div>
        </div>

    {else}
        <div class="b-profile-avatar">
            <img src="{$oUserProfile->getProfileAvatarPath(100)}" alt="avatar" itemprop="photo" />
        </div>
    {/if}

	<div id="vote_area_user_{$oUserProfile->getId()}" class="vote {if $oUserProfile->getRating()>=0}vote-count-positive{else}vote-count-negative{/if} {if $oVote} voted {if $oVote->getDirection()>0}voted-up{elseif $oVote->getDirection()<0}voted-down{/if}{/if}">
		<div class="vote-label">{$aLang.user_rating}</div>
		<a href="#" class="vote-up" onclick="return ls.vote.vote({$oUserProfile->getId()},this,1,'user');"></a>
		<a href="#" class="vote-down" onclick="return ls.vote.vote({$oUserProfile->getId()},this,-1,'user');"></a>
		<div id="vote_total_user_{$oUserProfile->getId()}" class="vote-count count" title="{$aLang.user_vote_count}: {$oUserProfile->getCountVote()}">{if $oUserProfile->getRating() > 0}+{/if}{$oUserProfile->getRating()}</div>
	</div>
	
	<div class="strength">
		<div class="vote-label">{$aLang.user_skill}</div>
		<div class="count" id="user_skill_{$oUserProfile->getId()}">{$oUserProfile->getSkill()}</div>
	</div>
	
	<h2 class="page-header user-login word-wrap {if !$oUserProfile->getProfileName()}no-user-name{/if}" itemprop="nickname">{$oUserProfile->getLogin()}</h2>
	
	{if $oUserProfile->getProfileName()}
		<p class="user-name" itemprop="name">{$oUserProfile->getProfileName()|escape:'html'}</p>
	{/if}
	
	{hook run='profile_top_end' oUserProfile=$oUserProfile}
</div>