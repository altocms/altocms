{extends file="_index.tpl"}

{block name="layout_vars"}
    {if $sMode=='add'}
        {$menu_content='create'}
    {/if}
{/block}

{block name="layout_content"}
    {include file='topics/topic.edit.tpl'}
{/block}
