{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$menu_content="feed"}
{/block}

{block name="layout_content"}

    {include file='topics/topic.list.tpl'}

    {if count($aTopics)}
        {if !$bDisableGetMoreButton}
            <div class="js-userfeed-topics"></div>
            <a class="btn btn-success btn-lg btn-block js-userfeed-getmore" data-last-id="{$iUserfeedLastId}">
                {$aLang.userfeed_get_more} &darr;
            </a>
        {/if}
    {/if}

{/block}
