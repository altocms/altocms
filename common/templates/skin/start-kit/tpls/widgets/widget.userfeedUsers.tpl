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
                        <input type="text" autocomplete="off" class="form-control autocomplete-users js-userfeed-input"/>
                        <span class="input-group-btn">
                            <a href="#" onclick="ls.userfeed.appendUser(); return false;" class="btn btn-default">{$aLang.userfeed_block_users_append}</a>
                        </span>
                    </div>

                    <ul class="list-unstyled max-height-200 js-userfeed-userlist">
                        <li class="checkbox js-userfeed-item-empty" style="display: none;">
                            <label>
                                <input type="checkbox" checked="checked" />
                                <a href="" title=""><img src="" alt="avatar" class="avatar"/></a>
                                <a href=""></a>
                            </label>
                        </li>
                        {foreach $aUserfeedSubscribedUsers as $oUser}
                            {$iUserId=$oUser->getId()}

                            {if !isset($aUserfeedFriends.$iUserId)}
                                <li class="checkbox js-userfeed-item" data-user-id="{$iUserId}">
                                    <label>
                                        <input type="checkbox" checked="checked" />
                                        <a href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                                    src="{$oUser->getAvatarUrl(24)}" alt="avatar"
                                                    class="avatar"/></a>
                                        <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                    </label>
                                </li>
                            {/if}
                        {/foreach}

                    </ul>
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

                    <ul class="list-unstyled max-height-200 js-userfeed-friendlist">
                        {foreach $aUserfeedFriends as $oUser}
                            {$iUserId=$oUser->getId()}
                            <li class="checkbox js-userfeed-item" data-user-id="{$iUserId}">
                                <label>
                                    <input type="checkbox" {if isset($aUserfeedSubscribedUsers.$iUserId)} checked="checked"{/if}/>
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
{/if}
