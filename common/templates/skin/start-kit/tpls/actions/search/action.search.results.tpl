{extends file="_index.tpl"}

{block name="layout_content"}
    <div class="page-header">
        <div class=" header">{$aLang.search_results}</div>
    </div>

    <div class="panel panel-default panel-search raised">

        <div class="panel-body">
            <form action="{router page='search'}" class="search">
                {hook run='search_form_begin'}
                <input type="text" value="{$aReq.q|escape:'html'}" placeholder="{$aLang.search}" maxlength="255" name="q"
                       class="form-control">
                {hook run='search_form_end'}
            </form>
            <br/>

            <ul class="nav nav-pills">
                {foreach $aRes.aCounts as $sType=>$iCount}
                    <li {if $aReq.sType == $sType}class="active"{/if}>
                        <a href="{router page='search'}{$sType}/?q={$aReq.q|escape:'html'}" data-search-type="{$sType}" class="js-search-link">
                            {if $sType=="topics"}
                                {$aLang.search_found_topics}
                            {elseif $sType=="comments"}
                                {$aLang.search_found_comments}
                            {else}
                                {hook run='search_result_item' sType=$sType}
                            {/if}
                            {if $iCount}({$iCount}){/if}
                        </a>
                    </li>
                {/foreach}
            </ul>

        </div>

    </div>

    {if $bIsResults}
        {if $aReq.sType == 'topics'}
            {include file='topics/topic.list.tpl'}
        {elseif $aReq.sType == 'comments'}
            {include file='comments/comment.list.tpl'}
        {else}
            {hook run='search_result' sType=$aReq.sType}
        {/if}
    {else}
        <div class="panel panel-default panel-search raised">
            <div class="panel-body">
                {$aLang.search_results_empty}
            </div>
        </div>
    {/if}

{/block}
