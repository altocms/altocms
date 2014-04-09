{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}
    <div class="page-header">
        <h1>{$aLang.user_list}:
            <span class="text-muted">{$oCity->getName()|escape:'html'}{if $aPaging} ({$aPaging.iCount}){/if}</span>
        </h1>
    </div>
    {include file='commons/common.user_list.tpl' aUsersList=$aUsersCity}

{/block}
