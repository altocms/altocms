{extends file="_index.tpl"}

{block name="layout_content"}

    <div class="page-header">
        <div class=" header">{$aLang.comments_all}</div>
    </div>
    {include file='comments/comment.list.tpl'}

{/block}
