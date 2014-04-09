{if $aUsersList}
    <ul class="list-unstyled list-inline user-list-avatar">
        {foreach $aUsersList as $oUserList}
            {$oSession=$oUserList->getSession()}
            <li>
                <a href="{$oUserList->getProfileUrl()}" title="{$oUserList->getDisplayName()}"><img
                            src="{$oUserList->getAvatarUrl(48)}" alt="{$oUserList->getDisplayName()}"
                            class="avatar"/></a>
            </li>
        {/foreach}
    </ul>
{else}
    {if $sUserListEmpty}
        <div class="notice-empty">{$sUserListEmpty}</div>
    {else}
        <div class="notice-empty">{$aLang.user_empty}</div>
    {/if}
{/if}

{include file='commons/common.pagination.tpl' aPaging=$aPaging}
