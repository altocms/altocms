{**
 * Список пользователей
 *}


{if $smarty.cookies.view==2}

    
    {**
     * список таблица
     *}
    <table class="table table-users">
        {if $bUsersUseOrder}
            <thead>
                <tr>
                    <th class="cell-name cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_login'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_login&order_way={if $sUsersOrder=='user_login'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_login'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user}</span></a>
                        </div>
                    </th>
                    <th class="cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_date_register'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_date_register&order_way={if $sUsersOrder=='user_date_register'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_date_register'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_date_registration}</span></a>
                        </div>
                    </th>
                    <th>{$aLang.user_date_last}</th>
                    <th class="cell-skill cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_skill'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_skill&order_way={if $sUsersOrder=='user_skill'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_skill'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_skill}</span></a>
                        </div>
                    </th>
                    <th class="cell-rating cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_rating'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_rating&order_way={if $sUsersOrder=='user_rating'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_rating'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_rating}</span></a>
                        </div>
                    </th>
                </tr>
            </thead>
        {else}
            <thead>
                <tr>
                    <th class="cell-name cell-tab"><div class="cell-tab-inner">{$aLang.user}</div></th>
                    <th class="cell-date cell-tab"><div class="cell-tab-inner">{$aLang.user_date_registration}</div></th>
                    <th class="cell-date cell-tab"><div class="cell-tab-inner">{$aLang.user_date_last}</div></th>
                    <th class="cell-skill cell-tab"><div class="cell-tab-inner">{$aLang.user_skill}</div></th>
                    <th class="cell-rating cell-tab"><div class="cell-tab-inner">{$aLang.user_rating}</div></th>
                </tr>
            </thead>
        {/if}

        <tbody>
            {foreach $aUsersList as $oUserList}
                {$oSession = $oUserList->getSession()}
                {$oUserNote = $oUserList->getUserNote()}

                <tr>
                    <td class="cell-name">
                        <a href="{$oUserList->getProfileUrl()}"><img src="{$oUserList->getAvatarUrl(24)}" alt="avatar" class="table-avatar" /></a>
                        <p class="username word-wrap"><a href="{$oUserList->getProfileUrl()}">{$oUserList->getLogin()}</a>
                            {if $oUserNote}
                                <i class="icon-comment js-tooltip" title="{$oUserNote->getText()|escape:'html'}"></i>
                            {/if}
                        </p>
                    </td>
                    <td class="cell-date {if $sUsersOrder=='user_date_register'}{$sUsersOrderWay}{/if}">{date_format date=$oUserList->getDateRegister() format="d.m.y, H:i"}</td>
                    <td class="cell-date">{if $oSession}{date_format date=$oSession->getDateLast() format="d.m.y, H:i"}{/if}</td>
                    <td class="cell-skill {if $sUsersOrder=='user_skill'}{$sUsersOrderWay}{/if}">{$oUserList->getSkill()}</td>
                    <td class="cell-rating {if $sUsersOrder=='user_rating'}{$sUsersOrderWay}{/if}">{$oUserList->getRating()}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="5">
                        {if $sUserListEmpty}
                            {$sUserListEmpty}
                        {else}
                            {$aLang.user_empty}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>



{else}


    
    {**
     * список пользователей плиткой
     *}
    <table class="table table-users">
        {if $bUsersUseOrder}
            <thead>
                <tr>
                    <th class="cell-sort">
                        {$aLang.sort_by}:
                    </th>
                    <th class="cell-name cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_login'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_login&order_way={if $sUsersOrder=='user_login'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_login'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user}</span></a>
                        </div>
                    </th>
                    <th class="cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_date_register'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_date_register&order_way={if $sUsersOrder=='user_date_register'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_date_register'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_date_registration}</span></a>
                        </div>
                    </th>
                    <th>{$aLang.user_date_last}</th>
                    <th class="cell-skill cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_skill'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_skill&order_way={if $sUsersOrder=='user_skill'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_skill'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_skill}</span></a>
                        </div>
                    </th>
                    <th class="cell-rating cell-tab">
                        <div class="cell-tab-inner {if $sUsersOrder=='user_rating'}active{/if}">
                            <a href="{$sUsersRootPage}?order=user_rating&order_way={if $sUsersOrder=='user_rating'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}" {if $sUsersOrder=='user_rating'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_rating}</span></a>
                        </div>
                    </th>
                </tr>
            </thead>
        {else}
            <thead>
                <tr>
                    <th class="cell-sort">{$aLang.sort_by}:</th>
                    <th class="cell-name cell-tab"><div class="cell-tab-inner">{$aLang.user}</div></th>
                    <th class="cell-date cell-tab"><div class="cell-tab-inner">{$aLang.user_date_registration}</div></th>
                    <th class="cell-date cell-tab"><div class="cell-tab-inner">{$aLang.user_date_last}</div></th>
                    <th class="cell-skill cell-tab"><div class="cell-tab-inner">{$aLang.user_skill}</div></th>
                    <th class="cell-rating cell-tab"><div class="cell-tab-inner">{$aLang.user_rating}</div></th>
                </tr>
            </thead>
        {/if}
    </table>

    {foreach $aUsersList as $oUserList}
        {$oSession = $oUserList->getSession()}
        {$oUserNote = $oUserList->getUserNote()}

        <div class="userlist-grid user">
            <img src="{$oUserList->getAvatarUrl(100)}" alt="avatar" class="userlist-grid avatar" />

            <a class="user-info" href="{$oUserList->getProfileUrl()}">
                <div class="rating">{$oUserList->getRating()}</div>

                <div class="links">
                    {if $oUserList->getProfileName()}
                        <strong>{$oUserList->getProfileName()}</strong><br />
                        <i class="icon-native-user-list-user"></i>{$oUserList->getLogin()}
                    {else}
                        <strong>{$oUserList->getLogin()}</strong>
                    {/if}
                </div>
            </a>
        </div>
    {foreachelse}
        {if $sUserListEmpty}
            {$sUserListEmpty}
        {else}
            {$aLang.user_empty}
        {/if}
    {/foreach}
{/if}

{include file='pagination.tpl' aPaging=$aPaging}