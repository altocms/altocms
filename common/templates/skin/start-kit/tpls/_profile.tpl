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

                    {if $oUserProfile->getProfileName()}
                        <div class=" header user-login word-wrap" itemprop="nickname">{$oUserProfile->getDisplayName()}</div>

                        <p class="text-muted user-name" itemprop="name">{$oUserProfile->getProfileName()|escape:'html'}</p>
                    {else}
                        <div class=" header user-login word-wrap no-user-name" itemprop="name">{$oUserProfile->getDisplayName()}</div>
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
