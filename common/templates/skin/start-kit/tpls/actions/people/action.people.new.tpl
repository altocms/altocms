{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}
    <div class="page-header">
        <h1>{$aLang.people}</h1>
    </div>
    <table class="table table-hover table-users">
        <thead>
        <tr>
            <th class="cell-name">
                <small>{$aLang.user}</small>
            </th>
            <th class="cell-date">
                <small>{$aLang.user_date_registration}</small>
            </th>
            <th class="cell-skill">
                <small>{$aLang.user_skill}</small>
            </th>
            <th class="cell-rating">
                <small>{$aLang.user_rating}</small>
            </th>
        </tr>
        </thead>

        <tbody>
        {if $aUsersRegister}
            {foreach $aUsersRegister as $oUserList}
                {$oSession=$oUserList->getSession()}
                {$oUserNote=$oUserList->getUserNote()}
                <tr>
                    <td class="cell-name">
                        <a href="{$oUserList->getProfileUrl()}"><img src="{$oUserList->getAvatarUrl(48)}" alt="avatar"
                                                                     class="avatar"/></a>

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
                    <td class="text-muted cell-date">
                        <small>{date_format date=$oUserList->getDateRegister() format="d.m.y, H:i"}</small>
                    </td>
                    <td class="text-info cell-skill">
                        <small>{$oUserList->getSkill()}</small>
                    </td>
                    <td class="cell-rating{if $oUserList->getRating() < 0} text-danger negative{else} text-success{/if}">
                        <small>{$oUserList->getRating()}</small>
                    </td>
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

    {include file='paging.tpl' aPaging=$aPaging}

{/block}
