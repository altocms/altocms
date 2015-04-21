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
                    <img src="{$oUserProfile->getAvatarUrl('big')}" alt="{$oUserProfile->getDisplayName()}" class="avatar" itemprop="photo"/>

                    {hook run='profile_header' oUserProfile=$oUserProfile oVote=$oVote}

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
