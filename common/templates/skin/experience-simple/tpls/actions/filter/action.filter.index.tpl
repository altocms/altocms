 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}
{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}
    {include file='topics/topic.list.tpl'}
{/block}
