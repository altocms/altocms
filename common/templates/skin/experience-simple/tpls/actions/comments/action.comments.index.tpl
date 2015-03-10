 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_pre_content"}
    <div class="panel panel-default panel-search flat">
        <div class="panel-body">
            <h2 class="panel-header">
                {$aLang.comments_all}
            </h2>
        </div>
    </div>
{/block}

{block name="layout_content"}

    {include file='comments/comment.list.tpl'}

{/block}
