<section class="panel panel-default widget widget-type-activity">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.stream_block_users_friends}</h3>
        </header>

        <div class="widget-content">
            <p class="text-muted">
                <small>{$aLang.stream_settings_note_follow_friend}</small>
            </p>

            {if count($aStreamFriends)}
                <ul class="list-unstyled stream-settings-friends max-height-200">
                    {foreach $aStreamFriends as $oUser}
                        {$iUserId=$oUser->getId()}
                        <li class="checkbox">
                            <label>
                                <input class="streamUserCheckbox"
                                       type="checkbox"
                                       id="strm_u_{$iUserId}"
                                        {if isset($aStreamSubscribedUsers.$iUserId)} checked="checked"{/if}
                                       onClick="if (jQuery(this).prop('checked')) { ls.stream.subscribe({$iUserId}) } else { ls.stream.unsubscribe({$iUserId}) } "/>
                                <a href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                            src="{$oUser->getAvatarUrl(24)}" alt="avatar"
                                            class="avatar"/></a>
                                <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                            </label>
                        </li>
                    {/foreach}
                </ul>
            {else}
                <p class="text-muted">
                    <small>{$aLang.stream_no_subscribed_users}</small>
                </p>
            {/if}
        </div>

    </div>
</section>
