 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}


{block name="layout_content"}

    <div class="panel panel-default panel-search raised">

        <div class="panel-body">

            <div class="panel-header">
                {$aLang.search}
            </div>

            {hook run='search_begin'}
            <form action="{router page='search'}topics/" class="search">
                {hook run='search_form_begin'}
                <div class="form-group">
                    <input type="text" placeholder="{$aLang.search}" maxlength="255" name="q" class="form-control" value="{$aReq.q|escape:'html'}">
                </div>
                {hook run='search_form_end'}
            </form>
            {hook run='search_end'}


        </div>


    </div>


{/block}
