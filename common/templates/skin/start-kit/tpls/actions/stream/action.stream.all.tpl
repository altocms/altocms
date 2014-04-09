{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="stream"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <div class="page-header">
        <h1>{$aLang.stream_menu}</h1>
    </div>
    {if count($aStreamEvents)}
        <ul class="list-unstyled stream-list" id="stream-list">
            {include file='actions/stream/action.stream.events.tpl'}
        </ul>
        {if !$bDisableGetMoreButton}
            <input type="hidden" id="stream_last_id" value="{$iStreamLastId}"/>
            <a class="btn btn-success btn-lg btn-block" id="stream_get_more"
               href="javascript:ls.stream.getMoreAll()">{$aLang.stream_get_more} &darr;</a>
        {/if}
    {else}
        <div class="alert alert-info">
            {$aLang.stream_no_events}
        </div>
    {/if}
{/block}
