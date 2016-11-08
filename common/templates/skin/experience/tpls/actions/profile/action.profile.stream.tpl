 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_profile.tpl"}

{block name="layout_profile_content"}

<div class="panel panel-default panel-table raised">

    <div class="panel-body">

    {if count($aStreamEvents)}
        <ul class="list-unstyled stream-list" id="stream-list">
            {include file='actions/stream/action.stream.events.tpl'}
        </ul>

    {else}
        {$aLang.stream_no_events}
    {/if}
    </div>

    <div class="panel-footer">
        {if !$bDisableGetMoreButton}
            <input type="hidden" id="stream_last_id" value="{$iStreamLastId}"/>
            <a class="small link link-gray link-clear link-lead activity-get-more" id="stream_get_more"
               href="javascript:ls.userstream.getMoreByUser({$oUserProfile->getId()})">
                <i class="fa fa-eject fa-rotate-180"></i>&nbsp;&nbsp;{$aLang.stream_get_more}</a>
        {/if}
    </div>
</div>
{/block}
