{include file='header.tpl'}



<h2 class="page-header">{$aLang.search_results}</h2>


<form action="{router page='search'}topics/" class="search">
	{hook run='search_form_begin'}
	<input type="text" value="{$aReq.q|escape:'html'}" placeholder="{$aLang.search}" maxlength="255" name="q" class="input-text">
	<input type="submit" value="" title="{$aLang.search_submit}" class="input-submit icon icon-search">
	{hook run='search_form_end'}
</form>


{if $bIsResults}
	<ul class="nav nav-pills">
        <li {if $aReq.sType == 'topics'}class="active"{/if}>
            <a href="{router page="search"}topics/?q={$aReq.q|escape:'html'}">{$aLang.search_found_topics}{if $aReq.sType == 'topics'} ({$aRes.count}){/if}</a>
        </li>
        <li {if $aReq.sType == 'comments'}class="active"{/if}>
            <a href="{router page="search"}comments/?q={$aReq.q|escape:'html'}">{$aLang.search_found_comments}{if $aReq.sType == 'comments'} ({$aRes.count}){/if}</a>
        </li>
	</ul>
	

	{if $aReq.sType == 'topics'}
		{include file='topic_list.tpl'}
	{elseif $aReq.sType == 'comments'}
		{include file='comment_list.tpl'}
	{else}
		{hook run='search_result' sType=$aReq.sType}
	{/if}
{else}
	{$aLang.search_results_empty}
{/if}



{include file='footer.tpl'}