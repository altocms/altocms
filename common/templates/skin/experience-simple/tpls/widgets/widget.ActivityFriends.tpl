 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if E::IsUser()}

<script>
    $(function(){
        $('.stream-settings-friends input:checkbox').off('ifChanged').on('ifChanged', function(e) {
            $(this).trigger('change');
        })
        jQuery('.widget-userfeed-friends [data-alto-role="popover"]')
                .altoPopover(false);
    })
</script>

<div class="panel panel-default sidebar flat widget widget-userfeed widget-userfeed-friends">
    <div class="panel-body pab24">
        <h4 class="panel-header">
            <i class="fa fa-users"></i>
            {$aLang.stream_block_users_friends}
        </h4>

        <div class="widget-content">
            <p class="text-muted">
                <small>{$aLang.stream_settings_note_follow_friend}</small>
            </p>

            {if count($aStreamFriends)}
                <ul class="list-unstyled stream-settings-friends max-height-200">
                    {foreach $aStreamFriends as $oUser}
                        {$iUserId=$oUser->getId()}
                        <li data-alto-role="popover"
                            data-api="user/{$oUser->getId()}/info"
                            data-api-param-tpl="default"
                            data-trigger="hover"
                            data-placement="left"
                            data-animation="true"
                            data-cache="true"
                            class="checkbox pal0">
                            <label>
                                <input class="streamUserCheckbox"
                                       type="checkbox"
                                       id="strm_u_{$iUserId}"
                                        {if isset($aStreamSubscribedUsers.$iUserId)} checked="checked"{/if}
                                       onchange="if (jQuery(this).prop('checked')) { ls.stream.subscribe({$iUserId}) } else { ls.stream.unsubscribe({$iUserId}, false) } "/>&nbsp;
                                <a
                                   href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                            src="{$oUser->getAvatarUrl('mini')}" alt="avatar"
                                            class="avatar"/></a>
                                <a
                                   href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                            </label>
                        </li>
                    {/foreach}
                </ul>
            {else}
                <div class="bg-warning">
                    {$aLang.stream_no_subscribed_users}
                </div>
            {/if}
        </div>

    </div>
</div>
{/if}