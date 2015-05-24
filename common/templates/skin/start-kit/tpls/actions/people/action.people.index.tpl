{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}
    <div class="content-inner-panel">
        <div class="page-header">
            <div class=" header">{$aLang.people}</div>
        </div>
        <form action="" method="POST" id="form-users-search" onsubmit="return false;" class="search-item">
            <input id="search-user-login" type="text" placeholder="{$aLang.user_search_title_hint}" autocomplete="off"
                   name="user_login" value="" class="form-control"
                   onkeyup="ls.timer.run('user-search', ls.user.searchUsers, ['#form-users-search']);">
            <ul id="user-prefix-filter" class="list-unstyled list-inline search-abc">
                <li class="active"><a href="#" class="link-dotted"
                                      onclick="return ls.user.searchUsersByPrefix('',this);">{$aLang.user_search_filter_all}</a>
                </li>
                {foreach $aPrefixUser as $sPrefixUser}
                    <li><a href="#" class="link-dotted"
                           onclick="return ls.user.searchUsersByPrefix('{$sPrefixUser}',this);">{$sPrefixUser}</a></li>
                {/foreach}
            </ul>
        </form>
        <div id="users-list-search" style="display:none;"></div>
        <div id="users-list-original">
            {router page='people' assign=sUsersRootPage}
            {include file='commons/common.user_list.tpl' aUsersList=$aUsersRating bUsersUseOrder=true sUsersRootPage=$sUsersRootPage}
        </div>
    </div>
    <div class="content-inner-paging">
        {include file='commons/common.pagination.tpl' aPaging=$aPaging}
    </div>
{/block}
