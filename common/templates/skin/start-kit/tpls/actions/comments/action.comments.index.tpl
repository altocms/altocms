{extends file="_index.tpl"}

{block name="layout_content"}

    <div class="page-header">
        <h1>{$aLang.comments_all}</h1>
    </div>
    {include file='comments/comment.list.tpl'}

{/block}
