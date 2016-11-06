 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $aUsersList}
    <div class="row friend-line">
        {foreach $aUsersList as $oUserList}
            <div class="col-xs-4 friend">
                <a href="{$oUserList->getProfileUrl()}" title="{$oUserList->getDisplayName()}">
                    <img src="{$oUserList->getAvatarUrl('big')}" {$oUserList->getAvatarImageSizeAttr('big')} alt="{$oUserList->getDisplayName()}" class="avatar"/>
                </a>
                <span class="{if $oUserList->isOnline()}success{else}danger{/if}"></span>
                {*{$oUserList->getDisplayName()}*}
            </div>
        {/foreach}
    </div>
{else}
    {if $sUserListEmpty}
        <div class="bg-warning">{$sUserListEmpty}</div>
    {else}
        <div class="bg-warning">{$aLang.user_empty}</div>
    {/if}
{/if}

{include file='commons/common.pagination.tpl' aPaging=$aPaging}