{extends file="_index.tpl"}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}
    <section class="panel panel-default widget widget-type-blacklist">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.talk_blacklist_title}</h3>
            </header>

            <div class="widget-content">
                <form onsubmit="return ls.talk.addToBlackList(this, '#user_black_list');">
                    <div class="form-group">
                        <label for="talk_blacklist_add">{$aLang.talk_balcklist_add_label}</label>
                        <input type="text" name="user_list" class="form-control autocomplete-users-sep"/>
                    </div>
                </form>

                <ul class="list-unstyled" id="user_black_list">
                    {foreach $aUsersBlacklist as $oUser}
                        <li id="blacklist_item_{$oUser->getId()}">
                            <a href="{$oUser->getProfileUrl()}" class="user">{$oUser->getDisplayName()}</a>
                            -
                            <a href="#" onclick="return ls.talk.removeFromBlackList('{$oUser->getId()}')" class="delete">{$aLang.blog_delete}</a>
                        </li>
                    {/foreach}
                    <li id="user_black_list_item_ID" style="display: none;">
                        <a href="URL" class="user">NAME</a>
                        -
                        <a href="#" onclick="return ls.talk.removeFromBlackList('ID')" class="delete">{$aLang.blog_delete}</a>
                    </li>
                </ul>
            </div>

        </div>
    </section>
{/block}
