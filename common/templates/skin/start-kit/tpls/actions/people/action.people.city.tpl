{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}
    <div class="page-header">
        <div class=" header">{$aLang.user_list}:
            <span class="text-muted">{$oCity->getName()|escape:'html'}{if $aPaging} ({$aPaging.iCount}){/if}</span>
        </div>
    </div>
    {include file='commons/common.user_list.tpl' aUsersList=$aUsersCity}

{/block}
