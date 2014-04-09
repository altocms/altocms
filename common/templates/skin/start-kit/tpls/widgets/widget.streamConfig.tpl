{if E::IsUser()}
    <section class="panel panel-default widget widget-type-activity">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.stream_block_config_title}</h3>
            </header>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.stream_settings_note_filter}</small>
                </p>

                <ul class="list-unstyled activity-settings-filter">
                    {foreach $aStreamEventTypes as $sType=>$aEventType}
                        {if !(Config::Get('module.stream.disable_vote_events') AND substr($sType, 0, 4) == 'vote')}
                            <li class="checkbox">
                                <label>
                                    <input class="streamEventTypeCheckbox"
                                           type="checkbox"
                                           id="strn_et_{$sType}"
                                           {if in_array($sType, $aStreamTypesList)}checked="checked"{/if}
                                           onClick="ls.stream.switchEventType('{$sType}')"/>
                                    {foreach langKey as $"stream_event_type_`$sType`"}
                                    {$aLang.$langKey}
                                </label>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>

        </div>
    </section>
    <section class="panel panel-default widget widget-type-activity">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.stream_block_users_title}</h3>
            </header>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.stream_settings_note_follow_user}</small>
                </p>

                <div class="input-group">
                    <input type="text" id="stream_users_complete" autocomplete="off"
                           class="form-control autocomplete-users input-text"/>
                    <span class="input-group-btn">
                        <a href="javascript:ls.stream.appendUser()"
                           class="btn btn-default">{$aLang.stream_block_config_append}</a>
                    </span>
                </div>

                {if count($aStreamSubscribedUsers)}
                    <ul id="stream_block_users_list" class="list-unstyled user-list-mini max-height-200">
                        {foreach $aStreamSubscribedUsers as $oUser}
                            {$iUserId=$oUser->getId()}
                            {if !isset($aStreamFriends.$iUserId)}
                                <li class="checkbox">
                                    <label>
                                        <input class="streamUserCheckbox"
                                               type="checkbox"
                                               id="strm_u_{$iUserId}"
                                               checked="checked"
                                               onClick="if (jQuery(this).prop('checked')) { ls.stream.subscribe({$iUserId}) } else { ls.stream.unsubscribe({$iUserId}) } "/>
                                        <a href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                                    src="{$oUser->getAvatarUrl(24)}" alt="avatar"
                                                    class="avatar"/></a>
                                        <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                    </label>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {else}
                    <ul id="stream_block_users_list" class="list-unstyled max-height-200"></ul>
                    <p id="stream_no_subscribed_users" class="text-muted">
                        <small>{$aLang.stream_no_subscribed_users}</small>
                    </p>
                {/if}
            </div>

        </div>
    </section>
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
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('#stream_users_complete').keydown(function (event) {
                if (event.which == 13) {
                    ls.stream.appendUser()
                }
            });
        });
    </script>
{/if}
