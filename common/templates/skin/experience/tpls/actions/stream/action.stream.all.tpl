 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <div class="panel panel-default panel-table raised">

        <div class="panel-body">

            <h2 class="panel-header">
                {$aLang.stream_menu}
            </h2>

            {if count($aStreamEvents) == 0}
                <div class="bg-warning">
                    {$aLang.stream_no_events}
                </div>
            {/if}
        </div>

        <div class="panel-footer">
            {include file='menus/menu.stream.tpl'}
        </div>

    </div>


    {if count($aStreamEvents)}
        <div class="panel panel-default panel-table raised">

            <div class="panel-body">
                <ul class="list-unstyled stream-list" id="activity-event-list">
                    {include file='actions/stream/action.stream.events.tpl'}
                </ul>
            </div>

            <div class="panel-footer">
                {if !$bDisableGetMoreButton}
                    <input type="hidden" id="activity-last-id" value="{$iStreamLastId}"/>
                    <a class="small link link-gray link-clear link-lead activity-get-more" id="stream_get_more"
                       data-param="all"
                       href="javascript:ls.stream.getMore('#stream_get_more')">
                        <i class="fa fa-eject fa-rotate-180"></i>&nbsp;&nbsp;{$aLang.stream_get_more}</a>
                {/if}
            </div>
        </div>
    {/if}

{/block}
