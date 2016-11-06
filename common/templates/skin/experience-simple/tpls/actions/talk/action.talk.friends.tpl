 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default panel-table flat">

    <div class="panel-body">

        <div class="panel-header">
            <i class="fa fa-users"></i>&nbsp;{$aLang.widget_friends}
        </div>

        <script>
            $(function(){
                $('#friends input:checkbox').on('ifChanged', function(e) {
                    $('#friends input:checkbox').trigger('change');
                })
            })
        </script>

        <div class="panel-content" id="widget_talk_friends_content" style="display: none;">
            {if $aUsersFriend}
                <ul id="friends" class="list-unstyled list-inline friend-list">
                    {foreach $aUsersFriend as $oFriend}
                        <li>
                            <div class="checkbox">
                                <label onclick=" $(this).find('input').trigger('change');">
                                    <input id="talk_friend_{$oFriend->getId()}" determinate="false" type="checkbox" value="{$oFriend->getLogin()}" name="friend[{$oFriend->getId()}]" class="input-checkbox"/>
                                    <label for="talk_friend_{$oFriend->getId()}" id="talk_friend_{$oFriend->getId()}_label">
                                        <img src="{$oFriend->getAvatarUrl('mini')}" {$oFriend->getAvatarImageSizeAttr('mini')} alt="avatar" class="avatar"/>
                                        {$oFriend->getDisplayName()}
                                    </label>
                                </label>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            {else}
                <div class="bg-warning">{$aLang.widget_friends_empty}</div>
            {/if}
        </div>

    </div>

    <div class="panel-footer par0">
        <ul class="pa0">
            <li><a href="#" id="friend_check_all"   onclick="$('#friends input:checkbox').iCheck('check')" style="display: none;" class="link link-light-gray link-lead link-clear">{$aLang.widget_friends_check}</a></li>
            <li><a href="#" id="friend_uncheck_all" onclick="$('#friends input:checkbox').iCheck('uncheck')" style="display: none;" class="link link-light-gray link-lead link-clear">{$aLang.widget_friends_uncheck}</a></li>
            <li class="pull-right marr0 pa0"><a href="#" class="link link-light-gray link-lead link-clear btn btn-gray" onclick="jQuery('#widget_talk_friends_content').add('#friend_check_all').add('#friend_uncheck_all').toggle(); return false; "><i class="fa fa-bars"></i>{$aLang.show_list}</a></li>
        </ul>

    </div>

</div>