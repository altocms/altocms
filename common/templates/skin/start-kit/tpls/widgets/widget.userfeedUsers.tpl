{if E::IsUser()}
    <section class="panel panel-default widget widget-type-activity">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.userfeed_block_users_title}</h3>
            </header>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.userfeed_settings_note_follow_user}</small>
                </p>

                <div class="stream-settings-userlist">
                    <div class="input-group">
                        <input type="text" id="userfeed_users_complete" autocomplete="off" class="form-control autocomplete-users input-text"/>
                        <span class="input-group-btn">
                            <a href="javascript:ls.userfeed.appendUser()" class="btn btn-default">{$aLang.userfeed_block_users_append}</a>
                        </span>
                    </div>

                    {if count($aUserfeedSubscribedUsers)}
                        <ul id="userfeed_block_users_list" class="list-unstyled user-list-mini max-height-200">
                            {foreach from=$aUserfeedSubscribedUsers item=oUser}
                                {$iUserId=$oUser->getId()}

                                {if !isset($aUserfeedFriends.$iUserId)}
                                    <li class="checkbox">
                                        <label>
                                            <input class="userfeedUserCheckbox"
                                                   type="checkbox"
                                                   id="usf_u_{$iUserId}"
                                                   checked="checked"
                                                   onClick="if (jQuery(this).prop('checked')) { ls.userfeed.subscribe('users',{$iUserId}) } else { ls.userfeed.unsubscribe('users',{$iUserId}) } "/>
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
                        <ul id="userfeed_block_users_list" class="list-unstyled max-height-200"></ul>
                    {/if}
                </div>
            </div>

        </div>
    </section>
    {if count($aUserfeedFriends)}
        <section class="panel panel-default widget widget-type-activity">
            <div class="panel-body">

                <header class="widget-header">
                    <h3 class="widget-title">{$aLang.userfeed_block_users_friends}</h3>
                </header>

                <div class="widget-content">
                    <p class="text-muted">
                        <small>{$aLang.userfeed_settings_note_follow_friend}</small>
                    </p>

                    <ul class="list-unstyled stream-settings-friends max-height-200">
                        {foreach $aUserfeedFriends as $oUser}
                            {$iUserId=$oUser->getId()}
                            <li class="checkbox">
                                <label>
                                    <input class="userfeedUserCheckbox"
                                           type="checkbox"
                                           id="usf_u_{$iUserId}"
                                            {if isset($aUserfeedSubscribedUsers.$iUserId)} checked="checked"{/if}
                                           onClick="if (jQuery(this).prop('checked')) { ls.userfeed.subscribe('users',{$iUserId}) } else { ls.userfeed.unsubscribe('users',{$iUserId}) } "/>
                                    <a href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                                src="{$oUser->getAvatarUrl(24)}" alt="avatar"
                                                class="avatar"/></a>
                                    <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                </div>

            </div>
        </section>
    {/if}
    <script language="JavaScript" type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('#userfeed_users_complete').keydown(function (event) {
                if (event.which == 13) {
                    ls.userfeed.appendUser()
                }
            });
        });
    </script>
{/if}
