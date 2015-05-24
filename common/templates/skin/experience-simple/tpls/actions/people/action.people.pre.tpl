 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default panel-search flat">

    <div class="panel-body">

        <div class="panel-header">
            {if $header}
                {$header}
            {else}
                {$aLang.people}
            {/if}
        </div>

        <ul id="user-prefix-filter" class="abc">
            <li class="active">
                <a href="#" class="link link-dual link-lead"
                   onclick="return ls.user.searchUsersByPrefix('',this);">
                    {$aLang.user_search_filter_all}
                </a>
            </li>

            {foreach $aPrefixUser as $sPrefixUser}
                <li>
                    <a href="#" class="link link-dual link-lead"
                       onclick="return ls.user.searchUsersByPrefix('{$sPrefixUser}',this);">
                        {$sPrefixUser}
                    </a>
                </li>
            {/foreach}
        </ul>

        <form action="" method="POST" id="form-users-search" onsubmit="return false;" class="search-item">
            <label>
                <input id="search-user-login" type="text" placeholder="{$aLang.user_search_title_hint}" autocomplete="off"
                       name="user_login" value="" class="form-control"
                       onkeyup="ls.timer.run('user-search', ls.user.searchUsers, ['#form-users-search']);">
            </label>
        </form>

    </div>

    {include file="menus/menu.people.tpl"}

</div>