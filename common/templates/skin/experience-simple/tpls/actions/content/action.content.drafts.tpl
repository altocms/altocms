 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

 {block name="layout_vars"}
     {$menu="topics"}
 {/block}

{block name="layout_content"}

    <div class="action-header">
        {include file='menus/menu.content-create.tpl'}
    </div>
    {include file='topics/topic.list.tpl'}

{/block}
