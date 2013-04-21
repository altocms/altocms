{extends file='_index.tpl'}

{block name="content"}


<h2 class="page-header">{$aLang.user_list}: <span>{$oCountry->getName()|escape:'html'}{if $aPaging} ({$aPaging.iCount}){/if}</span></h2>

{include file='user_list.tpl' aUsersList=$aUsersCountry}

{/block}