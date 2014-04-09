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
            <th class="cell-skill">
                <small>
                    <a href="{$sUsersRootPage}?order=user_skill&order_way={if $sUsersOrder=='user_skill'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
                       {if $sUsersOrder=='user_skill'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_skill}</span></a>
                </small>
            </th>
            <th class="cell-rating">
                <small>
                    <a href="{$sUsersRootPage}?order=user_rating&order_way={if $sUsersOrder=='user_rating'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
                       {if $sUsersOrder=='user_rating'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_rating}</span></a>
                </small>
            </th>
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
            <th class="cell-skill">
                <small>{$aLang.user_skill}</small>
            </th>
            <th class="cell-rating">
                <small>{$aLang.user_rating}</small>
            </th>
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
                    <a href="{$oUserList->getProfileUrl()}"><img src="{$oUserList->getAvatarUrl(48)}"
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
                <td class="small text-info cell-skill">{$oUserList->getSkill()}</td>
                <td class="small cell-rating{if $oUserList->getRating() < 0} text-danger negative{else} text-success{/if}">{$oUserList->getRating()}</td>
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
