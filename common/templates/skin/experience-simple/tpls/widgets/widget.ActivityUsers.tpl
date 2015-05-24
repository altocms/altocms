 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if E::IsUser()}

<script>
    $(function(){
        $('#stream_block_users_list input:checkbox').off('ifChanged').on('ifChanged', function(e) {
            $(this).trigger('change');
        })

        jQuery('.widget-userfeed-activity [data-alto-role="popover"]')
                .altoPopover(false);

        ls.stream.options.elements.userItem = function(el) {
            var t = $('<li class="checkbox pal0">'+
            '<label>' +
            '<input class="streamUserCheckbox" ' +
            'type="checkbox" id="strm_u_' + el.uid  + '" checked="checked" />&nbsp;' +
            '<a href="' + el.user_web_path + '" title="' + el.user_login + '"><img style="width: 24px;" src="'+el.user_avatar_48+'" alt="avatar" class="avatar"/></a>' +
            '<a href="' + el.user_web_path + '">' + el.user_login + '</a>' +
            '</label>' +
            '</li>')
                    t.find('input.streamUserCheckbox')
                    .on('change', function() {
                if (jQuery(this).prop('checked')) { ls.stream.subscribe(el.uid) } else { ls.stream.unsubscribe(el.uid, true) }
            }).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue'
            }).on('ifChanged', function(e) {
                                $(this).trigger('change');
                            }).end();

            return t;
        }
    })
</script>

<div class="panel panel-default sidebar flat widget widget-userfeed widget-userfeed-activity">
    <div class="panel-body pab24">
        <div class="panel-header">
            <i class="fa fa-users"></i>
            {$aLang.stream_block_users_title}
        </div>


        <div class="widget-content">
            <p class="text-muted">
                <small>{$aLang.stream_settings_note_follow_user}</small>
            </p>

            <div class="input-group">
                <input type="text" id="activity-block-users-input" autocomplete="off"
                       class="form-control autocomplete-users input-text"/>
                    <span class="input-group-addon for-button">
                        <a href="#" onclick="ls.stream.appendUser(); return false;" class="btn btn-gray">{$aLang.userfeed_block_users_append}</a>
                    </span>
                    {*<span class="input-group-btn">*}
                        {*<a href="javascript:ls.stream.appendUser()"*}
                           {*class="btn btn-default">{$aLang.stream_block_config_append}</a>*}
                    {*</span>*}
            </div>

            {if count($aStreamSubscribedUsers)}
                <ul id="stream_block_users_list" class="list-unstyled user-list-mini max-height-200">
                    {foreach $aStreamSubscribedUsers as $oUser}
                        {$iUserId=$oUser->getId()}
                        {if !isset($aStreamFriends.$iUserId)}
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
                                           checked="checked"
                                           onchange="if (jQuery(this).prop('checked')) { ls.stream.subscribe({$iUserId}) } else { ls.stream.unsubscribe({$iUserId}, true) } "/>&nbsp;
                                    <a
                                       href="{$oUser->getProfileUrl()}" title="{$oUser->getDisplayName()}"><img
                                                src="{$oUser->getAvatarUrl('mini')}" alt="avatar"
                                                class="avatar"/></a>
                                    <a
                                       href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                </label>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            {/if}
                <ul id="activity-block-users" class="list-unstyled max-height-200"></ul>
        </div>

    </div>
</div>
{/if}