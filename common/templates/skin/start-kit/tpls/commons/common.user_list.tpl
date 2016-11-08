<table class="table table-users">
    {if $bUsersUseOrder}
        <thead>
        <tr>
            <th class="cell-name">
                <small>
                    <a href="{$sUsersRootPage}?order=user_login&order_way={if $sUsersOrder=='user_login'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
                       {if $sUsersOrder=='user_login'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user}</span></a>
                </small>
            </th>
            <th class="cell-date">
                <small>{$aLang.user_date_last}</small>
            </th>
            {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
        </tr>
        </thead>
    {else}
        <thead>
        <tr>
            <th class="cell-name">
                <small>{$aLang.user}</small>
            </th>
            <th class="cell-date">
                <small>{$aLang.user_date_last}</small>
            </th>
            {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
        </tr>
        </thead>
    {/if}

    <tbody>
    {if $aUsersList}
        {foreach $aUsersList as $oUserList}
            {$oSession=$oUserList->getSession()}
            {$oUserNote=$oUserList->getUserNote()}
            <tr>
                <td class="cell-name">
                    <a href="{$oUserList->getProfileUrl()}"><img src="{$oUserList->getAvatarUrl('medium')}" {$oUserList->getAvatarImageSizeAttr('medium')}
                                                                 alt="{$oUserList->getDisplayName()}"
                                                                 class="avatar visible-lg"/></a>

                    <div class="name {if !$oUserList->getProfileName()}no-realname{/if}">
                        <p class="username">
                            <a href="{$oUserList->getProfileUrl()}">{$oUserList->getDisplayName()}</a>
                            {if $oUserNote}
                                <span class="glyphicon glyphicon-comment text-muted js-infobox"
                                      title="{$oUserNote->getText()|escape:'html'}"></span>
                            {/if}
                        </p>
                        {if $oUserList->getProfileName()}
                            <p class="text-muted realname">
                            <small>{$oUserList->getProfileName()}</small></p>{/if}
                    </div>
                </td>
                <td class="small text-muted cell-date">
                    {if $oSession}
                        {date_format date=$oSession->getDateLast() hours_back="12" minutes_back="60" now="60" day="day H:i" format="d.m.y, H:i"}
                    {/if}
                </td>
                {hook run='user_list_line' oUserList=$oUserList}
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="5">
                {if $sUserListEmpty}
                    {$sUserListEmpty}
                {else}
                    {$aLang.user_empty}
                {/if}
            </td>
        </tr>
    {/if}
    </tbody>
</table>
