{extends file="_profile.tpl"}

{block name="layout_profile_content"}

<div class="profile-content">
    {if count($aStreamEvents)}
        <ul class="list-unstyled stream-list" id="stream-list">
            {include file='actions/stream/action.stream.events.tpl'}
        </ul>
        {if !$bDisableGetMoreButton}
            <input type="hidden" id="stream_last_id" value="{$iStreamLastId}"/>
            <a class="btn btn-success btn-lg btn-block" id="stream_get_more"
               href="javascript:ls.stream.getMoreByUser({$oUserProfile->getId()})">{$aLang.stream_get_more} &darr;</a>
        {/if}
    {else}
        {$aLang.stream_no_events}
    {/if}
</div>

{/block}
