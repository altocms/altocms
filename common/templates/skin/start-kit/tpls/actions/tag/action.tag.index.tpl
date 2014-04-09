{extends file="_index.tpl"}

{block name="layout_content"}
    <form action="" method="GET" class="js-tag-search-form search-tags">
        <div class="form-group">
            <input type="text" name="tag" placeholder="{$aLang.block_tags_search}" value="{$sTag|escape:'html'}"
                   class="form-control autocomplete-tags js-tag-search"/>
        </div>
    </form>
    {include file='topics/topic.list.tpl'}

{/block}
