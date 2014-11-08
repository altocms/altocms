<script>
    $(function(){
        ls.stream.options.elements.userItem = function(el) {

            return $('<li class="checkbox">'+
                    '<label>'+
                        '<input class="streamUserCheckbox" type="checkbox" id="strm_u_' + el.uid  + '" checked="checked" '+
                        'onClick="if (jQuery(this).prop(\'checked\')) { ls.stream.subscribe(' + el.uid  + ') } else { ls.stream.unsubscribe(' + el.uid  + ') } "/>'+
                        '<a href="'+el.user_web_path+'" title="' + el.user_login + '">'+
                    '<img src="'+el.user_avatar_48+'" alt="avatar" class="avatar" style="height:24px;" /></a>'+
                    '<a href="' + el.user_web_path + '">' + el.user_login + '</a>'+
                    '</label>'+
                    '</li>');
        }
    })
</script>
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
                <input type="text" id="activity-block-users-input" autocomplete="off"
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
            <ul id="activity-block-users" class="list-unstyled user-list-mini max-height-200"></ul>
        </div>

    </div>
</section>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#activity-block-users-input').keydown(function (event) {
            if (event.which == 13) {
                ls.stream.appendUser()
            }
        });
    });
</script>
