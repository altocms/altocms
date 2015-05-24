 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_pre_content"}
    <div class="panel panel-default panel-search raised">
        <div class="panel-body">
            <h2 class="panel-header">
                {$oPage->getTitle()|escape:'html'}
            </h2>
        </div>
    </div>
{/block}

{block name="layout_content"}
    {include file="pages/page.type_default-show.tpl"}
{/block}