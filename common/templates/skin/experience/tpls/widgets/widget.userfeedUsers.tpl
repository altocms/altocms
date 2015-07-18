 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}

    <script>
        $(function(){
            $('.widget-userfeed input:checkbox').off('ifChanged').on('ifChanged', function(e) {
                $(this).trigger('change');
            })
        })
    </script>

<div class="panel panel-default sidebar raised widget widget-userfeed">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-comment-o"></i>
            {$aLang.userfeed_block_users_title}
        </div>

        <div class="widget-content">
            <p class="text-muted">
                <small>{$aLang.userfeed_settings_note_follow_user}</small>
            </p>

            <div class="stream-settings-userlist">
                <div class="input-group">
                    <input type="text" autocomplete="off" class="form-control autocomplete-users js-userfeed-input"/>
                    <span class="input-group-addon for-button">
                        <a href="#" onclick="ls.userfeed.appendUser(); return false;" class="btn btn-default">{$aLang.userfeed_block_users_append}</a>
                    </span>
                </div>
                <div>
                    <ul class="list-unstyled max-height-200 js-userfeed-userlist">
                        {foreach $aUserfeedSubscribedUsers as $oUser}
                            {$iUserId=$oUser->getId()}

                            {if !isset($aUserfeedFriends.$iUserId)}
                                <li class="checkbox pal0 js-userfeed-item" data-user-id="{$iUserId}">
                                    <label>
                                        <input type="checkbox" checked="checked" />&nbsp;
                                        <a href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                                    src="{$oUser->getAvatarUrl(24)}" alt="avatar"
                                                    class="avatar"/></a>
                                        <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                    </label>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                    <li class="checkbox pal0 js-userfeed-item-empty" style="display: none;">
                        <label>
                            <input type="checkbox" checked="checked" />&nbsp;
                            <a href="" title=""><img src="" alt="avatar" class="avatar"/></a>
                            <a href=""></a>
                        </label>
                    </li>
                </div>
            </div>
        </div>

    </div>
</div>




    {if count($aUserfeedFriends)}
        <div class="panel panel-default sidebar raised widget widget-userfeed">
            <div class="panel-body">
                <div class="panel-header">
                    <i class="fa fa-users"></i>
                    {$aLang.userfeed_block_users_friends}
                </div>

                <div class="widget-content">
                    <p class="text-muted">
                        <small>{$aLang.userfeed_settings_note_follow_friend}</small>
                    </p>

                    <ul class="list-unstyled max-height-200 js-userfeed-friendlist">
                        {foreach $aUserfeedFriends as $oUser}
                            {$iUserId=$oUser->getId()}
                            <li class="checkbox pal0 js-userfeed-item" data-user-id="{$iUserId}">
                                <label>
                                    <input type="checkbox" {if isset($aUserfeedSubscribedUsers.$iUserId)} checked="checked"{/if}/>&nbsp;
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
        </div>
    {/if}
{/if}
