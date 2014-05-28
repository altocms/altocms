<section class="panel panel-default widget widget-type-foldable widget-type-talk-friends">
    <div class="panel-body">

        <header class="widget-header">
            <a href="#" class="link-dotted"
               onclick="jQuery('#widget_talk_friends_content').toggle(); return false;">{$aLang.widget_friends}</a>
        </header>

        <div class="widget-content" id="widget_talk_friends_content">
            {if $aUsersFriend}
                <ul id="friends" class="list-unstyled friend-list">
                    {foreach $aUsersFriend as $oFriend}
                        <li>
                            <div class="checkbox">
                                <label>
                                    <input id="talk_friend_{$oFriend->getId()}" type="checkbox" name="friend[{$oFriend->getId()}]" class="input-checkbox"/>
                                    <label for="talk_friend_{$oFriend->getId()}" id="talk_friend_{$oFriend->getId()}_label">{$oFriend->getDisplayName()}</label>
                                </label>
                            </div>
                        </li>
                    {/foreach}
                </ul>
                <footer class="small text-muted">
                    <a href="#" id="friend_check_all" class="link-dotted">{$aLang.widget_friends_check}</a> |
                    <a href="#" id="friend_uncheck_all" class="link-dotted">{$aLang.widget_friends_uncheck}</a>
                </footer>
            {else}
                <div class="notice-empty">{$aLang.widget_friends_empty}</div>
            {/if}
        </div>

    </div>
</section>
