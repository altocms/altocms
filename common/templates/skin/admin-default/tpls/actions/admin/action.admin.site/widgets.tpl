{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon icon-plus"></i></a>
    </div>
{/block}

{block name="content-body"}
    <form action="{router page='admin'}site-widgets/" method="post" id="form_widgets_list" class="uniform">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="widget_action" value="">

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">

                <table class="table table-inbordered widgets-list">
                    <thead>
                    <tr>
                        <th>
                            <!-- input type="checkbox" name="" onclick="admin.selectAllRows(this);"/ -->
                        </th>
                        <th>{$aLang.action.admin.widget_name}</th>
                        <th>{$aLang.action.admin.widget_plugin}</th>
                        <th>{$aLang.action.admin.widget_group}</th>
                        <th>{$aLang.action.admin.widget_priority}</th>
                        <th>{$aLang.action.admin.widget_display}</th>
                        <th>{$aLang.action.admin.widget_showto}</th>
                        <th>{$aLang.action.admin.widget_active}</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    {foreach $aWidgetsList as $oWidget}
                        {$sClass=""}
                        {if $oWidget->isActive()}
                            {if !$oWidget->isDisplay(true)}{$sClass="warning"}{/if}
                        {else}
                        {/if}
                        <tr id="widget-{$oWidget->GetId()}" class="selectable {$sClass}">
                            <td class="check-row">
                                <input type="checkbox" name="widget_sel[{$oWidget->GetId()}]"
                                       class="form_widget_checkbox"/>
                            </td>
                            <td class="name">
                                <div class="i-title">{$oWidget->GetName()|escape:'html'}</div>
                                {if $oWidget->GetTitle()}
                                    {$oWidget->GetTitle()|escape:'html'}
                                {/if}
                            </td>
                            <td>
                                {$oWidget->GetPluginId()}
                            </td>
                            <td class="center">
                                {$oWidget->GetGroup()}
                            </td>
                            <td class="center">
                                {$oWidget->GetPriority()}
                            </td>
                            <td class="center">
                                {if $oWidget->GetPeriod()}
                                    {if $oWidget->GetDateFrom()}from {$oWidget->GetDateFrom()}<br/>{/if}
                                    {if $oWidget->GetDateUpto()}upto {$oWidget->GetDateUpto()}{/if}
                                {else}
                                    Always
                                {/if}
                            </td>
                            <td class="center">
                                {if $oWidget->GetVisitors() == 'admins'}
                                    {$aLang.action.admin.widget_showto_admins}
                                {elseif $oWidget->GetVisitors() == 'users'}
                                    {$aLang.action.admin.widget_showto_users}
                                {else}
                                    {$aLang.action.admin.widget_showto_all}
                                {/if}
                            </td>
                            <td class="center">
                                <div class="b-switch"
                                     onclick="admin.turnWidget('{$oWidget->GetId()}', '{!$oWidget->isActive()}'); return false;">
                                    <input type="checkbox" {if $oWidget->isActive()}checked{/if}
                                           name="b-switch-{$oWidget->GetId()}">
                                    <label><i></i></label>
                                </div>
                            </td>
                            <td class="center">
                                <a href="{router page="admin"}site-widgets/edit/{$oWidget->GetId()}"><i
                                            class="icon icon-note"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>

    </form>

    <script>
        var admin = admin || { };
        admin.turnWidget = function (widgetId, action) {
            if (!action) {
                action = 'deactivate';
            } else if (action != 'deactivate') {
                action = 'activate';
            }
            $('tr[id^=widget-] .check-row [type=checkbox]').prop("checked", false);
            $('tr[id^=widget-' + escapeId(widgetId) + '] .check-row [type=checkbox]').prop("checked", true);
            $('#form_widgets_list input[name=widget_action]').val(action);
            $('#form_widgets_list').submit();
        };
        function escapeId( myid ) {
            return myid.replace( /(:|\.|\[|\]|,)/g, "\\$1" );
        }
    </script>
{/block}
