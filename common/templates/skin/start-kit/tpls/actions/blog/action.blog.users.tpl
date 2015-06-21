{extends file="_index.tpl"}

{block name="layout_content"}
    <div class="page-header">
        <h1>{$aLang.blog_user_readers_all} ({$iCountBlogUsers}):
            <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a></h1>
    </div>
    {if $aBlogUsers}
        {$aUsersList=[]}
        {foreach $aBlogUsers as $oBlogUser}
            {$aUsersList[]=$oBlogUser->getUser()}
        {/foreach}
        {include file='commons/common.user_list.tpl' aUsersList=$aUsersList bUsersUseOrder=true sUsersRootPage=$sUsersRootPage}
        {include file='commons/common.pagination.tpl' aPaging=$aPaging}
    {else}
        {$aLang.blog_user_readers_empty}
    {/if}

{/block}
