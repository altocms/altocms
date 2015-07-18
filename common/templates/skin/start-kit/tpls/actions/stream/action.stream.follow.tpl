{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="stream"}

{/block}

{block name="layout_content"}
    <div class="page-header">
        <div class=" header">{$aLang.stream_menu}</div>
    </div>
    {if count($aStreamEvents)}
        <ul class="list-unstyled stream-list" id="activity-event-list">
            {include file='actions/stream/action.stream.events.tpl'}
        </ul>
        {if !$bDisableGetMoreButton}
            <a class="btn btn-success btn-lg btn-block"
               id="stream_get_more"
               data-param-type="follow" data-param-last_id="{$iStreamLastId}"
               href="#" onclick="ls.stream.getMore(this); return false;">
                {$aLang.stream_get_more} &darr;
            </a>
        {/if}
    {else}
        <div class="alert alert-info">
            {$aLang.stream_no_events}
        </div>
    {/if}

{/block}
