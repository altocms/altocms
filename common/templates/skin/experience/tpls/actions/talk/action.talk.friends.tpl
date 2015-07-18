 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="panel panel-default panel-table raised">

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
                                <label onclick=" $(this).find('input').trigger('change'); console.log('hello')">
                                    <input id="talk_friend_{$oFriend->getId()}" determinate="false"  value="{$oFriend->getLogin()}" type="checkbox" name="friend[{$oFriend->getId()}]" class="input-checkbox"/>
                                    <label for="talk_friend_{$oFriend->getId()}" id="talk_friend_{$oFriend->getId()}_label">{$oFriend->getDisplayName()}</label>
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

    <div class="panel-footer">
        <a href="#" id="friend_check_all"   onclick="$('#friends input:checkbox').iCheck('check')" style="display: none;" class="link link-light-gray link-lead link-clear">{$aLang.widget_friends_check}</a>&nbsp;
        <a href="#" id="friend_uncheck_all" onclick="$('#friends input:checkbox').iCheck('uncheck')" style="display: none;" class="link link-light-gray link-lead link-clear">{$aLang.widget_friends_uncheck}</a>
        <a href="#" class="link link-light-gray link-lead link-clear pull-right marr0" onclick="
            jQuery('#widget_talk_friends_content').add('#friend_check_all').add('#friend_uncheck_all').toggle();
            return false;
        ">{$aLang.show_list}</a>
    </div>

</div>