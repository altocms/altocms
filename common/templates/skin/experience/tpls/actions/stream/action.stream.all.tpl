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

            <div class="panel-header">
                {$aLang.stream_menu}
            </div>

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
                    <a class="small link link-gray link-clear link-lead activity-get-more"
                       style="display: block;"
                       id="stream_get_more"
                       data-param-type="all" data-param-last_id="{$iStreamLastId}"
                       href="#" onclick="ls.stream.getMore(this); return false;">
                        <i class="fa fa-eject fa-rotate-180"></i>&nbsp;&nbsp;{$aLang.stream_get_more}
                    </a>
                {/if}
            </div>
        </div>
    {/if}

{/block}
