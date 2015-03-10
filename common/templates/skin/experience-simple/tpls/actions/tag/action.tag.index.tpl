 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}


{block name="layout_content"}

    <div class="panel panel-default panel-search flat">

        <div class="panel-body">

            <h2 class="panel-header">
                {$aLang.search}
            </h2>

            {hook run='search_begin'}
            <form action="" method="GET" class="js-tag-search-form search-tags">
                <div class="form-group">
                    <input type="text" name="tag" placeholder="{$aLang.widget_tags_search}" value="{$sTag|escape:'html'}"
                           class="form-control autocomplete-tags js-tag-search"/>
                </div>
            </form>
            {hook run='search_end'}


        </div>


    </div>

    {include file='topics/topic.list.tpl'}
{/block}