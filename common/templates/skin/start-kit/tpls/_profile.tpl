{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
    {$oSession=$oUserProfile->getSession()}
    {$oVote=$oUserProfile->getVote()}
    {$oGeoTarget=$oUserProfile->getGeoTarget()}
{/block}

{block name="layout_content"}
    <div class="profile">
        <div class="profile-header">
            {block name="layout_profile_header"}
                {hook run='profile_header_begin'}

                <div class="profile-header-info">
                    <img src="{$oUserProfile->getAvatarUrl(64)}" alt="{$oUserProfile->getDisplayName()}" class="avatar" itemprop="photo"/>

                    {$sClasses = ''}
                    {if $oUserProfile->getRating()>=0}
                        {$sClasses = "$sClasses vote-count-positive "}
                    {else}
                        {$sClasses = "$sClasses vote-count-negative "}
                    {/if}
                    {if $oVote AND ($oVote->getDirection()>0)}
                        {$sClasses = "$sClasses voted voted-up "}
                    {elseif $oVote AND ($oVote->getDirection()<0)}
                        {$sClasses = "$sClasses voted voted-down "}
                    {elseif $oVote}
                        {$sClasses = "$sClasses voted "}
                    {/if}
                    <div class="small pull-right vote js-vote {$sClasses}" data-target-type="user" data-target-id="{$oUserProfile->getId()}">
                        <div class="text-muted vote-label">{$aLang.user_rating}</div>
                        <a href="#" class="vote-up js-vote-up" ><span class="glyphicon glyphicon-plus-sign"></span></a>

                        <div class="vote-count js-vote-rating" title="{$aLang.user_vote_count}: {$oUserProfile->getCountVote()}">
                            {if $oUserProfile->getRating() > 0}+{/if}{$oUserProfile->getRating()|number_format:{Config::Get('view.rating_length')}}
                        </div>
                        <a href="#" class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></a>
                    </div>

                    <div class="small pull-right strength">
                        <div class="text-muted vote-label">{$aLang.user_skill}</div>
                        <div class="text-info count" id="user_skill_{$oUserProfile->getId()}">{$oUserProfile->getSkill()|number_format:{Config::Get('view.skill_length')}}</div>
                    </div>

                    <h1 class="user-login word-wrap {if !$oUserProfile->getProfileName()}no-user-name{/if}"
                        itemprop="nickname">{$oUserProfile->getDisplayName()}</h1>

                    {if $oUserProfile->getProfileName()}
                        <p class="text-muted user-name" itemprop="name">{$oUserProfile->getProfileName()|escape:'html'}</p>
                    {/if}
                </div>

                <div class="clearfix"></div>

                {block name="layout_profile_submenu"}
                {/block}

                {hook run='profile_header_end'}
            {/block}
        </div>

        {block name="layout_profile_content"}
        {/block}

    </div>
{/block}
