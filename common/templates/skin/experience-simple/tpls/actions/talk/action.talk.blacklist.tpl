 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}
<div class="panel panel-default panel-table flat">
    <div class="panel-body">

            <div class="widget-title">{$aLang.talk_blacklist_title}</div>
        <br/>
            <div class="widget-content">

                <form onsubmit="return ls.talk.addToBlackList(this, '#user_black_list');">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.talk_balcklist_add_label}</span>
                            <input type="text" name="user_list" class="form-control autocomplete-users-sep"/>

                        </div>
                    </div>

                </form>

                <div id="speaker_list_block">
                    {if $aUsersBlacklist}
                        <ul class="list-unstyled text-muted list-inline" id="user_black_list">
                            {foreach $aUsersBlacklist as $oUser}
                                        <li id="user_black_list_item_{$oUser->getId()}">
                                            <a class="user" data-alto-role="popover"
                                               data-api="user/{$oUser->getId()}/info"
                                               href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                            - <a href="#" onclick="return ls.talk.removeFromBlackList('{$oUser->getId()}')"
                                                 class="link link-lead link-red-blue delete"><i class="fa fa-times"></i></a>
                                        </li>
                            {/foreach}
                            <li id="user_black_list_item_ID" style="display: none;">
                                <a href="URL" class="user">NAME</a>
                                -
                                <a href="#" onclick="return ls.talk.removeFromBlackList('ID')" class="link link-lead link-red-blue delete"><i class="fa fa-times"></i></a>
                            </li>
                        </ul>
                    {/if}
                </div>

            </div>

        </div>
</div>
{/block}
