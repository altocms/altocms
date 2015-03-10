 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

    <div class="panel panel-default panel-table flat">

        <div class="panel-body">

            <h2 class="panel-header">
                {$aLang.activity}
            </h2>

        </div>

        <div class="panel-footer">
            {include file='menus/menu.content-feed.tpl'}
        </div>

    </div>

    {include file='topics/topic.list.tpl'}

    {if count($aTopics)}
        {if !$bDisableGetMoreButton}
            <div class="js-userfeed-topics"></div>
            <a class="btn btn-success btn-lg btn-block js-userfeed-getmore" data-type="{$sFeedType}" data-last-id="{$iUserfeedLastId}">
                {$aLang.userfeed_get_more} &darr;
            </a>
        {/if}
    {/if}

{/block}
