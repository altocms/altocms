{extends file="_index.tpl"}
{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}
    {include file='topics/topic.list.tpl'}
{/block}
