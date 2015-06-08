{extends file="_index.tpl"}

{block name="layout_content"}
    <div class="page-header">
        <div class=" header">{$aLang.search}</div>
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

{/block}
