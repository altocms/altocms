{extends file='_index.tpl'}

{block name="content"}
    <h2 class="page-header">{$aLang.search_results}: <span>{$aReq.q|escape:'html'}</h2>

    <ul class="b-nav-pills">
        <li {if $aReq.sType == 'topics'}class="active"{/if}>
            <a href="{router page="search"}topics/?q={$aReq.q|escape:'html'}">{$aLang.search_found_topics}{if $aReq.sType == 'topics'} ({$aRes.count}){/if}</a>
        </li>
        <li {if $aReq.sType == 'comments'}class="active"{/if}>
            <a href="{router page="search"}comments/?q={$aReq.q|escape:'html'}">{$aLang.search_found_comments}{if $aReq.sType == 'comments'} ({$aRes.count}){/if}</a>
        </li>
    </ul>

    {if $bIsResults}

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

{/block}