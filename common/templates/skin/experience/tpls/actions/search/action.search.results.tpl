 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}


{block name="layout_content"}

    <div class="panel panel-default panel-search raised">

        <div class="panel-body">

            <div class="panel-header">{$aLang.search_results}</div>
            <form action="{router page='search'}topics/" class="search">
                {hook run='search_form_begin'}
                <input type="text" value="{$aReq.q|escape:'html'}" placeholder="{$aLang.search}" maxlength="255" name="q"
                       class="form-control">
                {hook run='search_form_end'}
            </form>

            {if !$bIsResults}
                {$aLang.search_results_empty}
            {/if}

        </div>


    </div>


    {if $bIsResults && $aRes.aCounts}
        <div class="row">
            <div class="col-lg-12 user-toggle-publication-block">

                {foreach $aRes.aCounts as $sType=>$iCount}
                        <a href="{router page='search'}{$sType}/?q={$aReq.q|escape:'html'}" class="btn btn-light-gray {if $aReq.sType == $sType}class="active"{/if}">
                            {$iCount}
                            {if $sType=="topics"}
                                {$aLang.search_results_count_topics}
                            {elseif $sType=="comments"}
                                {$aLang.search_results_count_comments}
                            {else}
                                {hook run='search_result_item' sType=$sType}
                            {/if}
                        </a>
                {/foreach}

            </div>

        </div>

    {/if}

    {if $bIsResults}
        {if $aReq.sType == 'topics'}
            {include file='topics/topic.list.tpl'}
        {elseif $aReq.sType == 'comments'}
            {include file='comments/comment.list.tpl'}
        {else}
            {hook run='search_result' sType=$aReq.sType}
        {/if}
    {/if}


{/block}
