{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary tip-top" onclick="ls.dashboard.showAddForm(); return false;"
           title="{$aLang.action.admin.user_field_add}"><i class="icon-plus-sign"></i></a>
    </div>
    <div class="btn-group">
        <a href="{router page='admin'}dashboard/index/" class="btn {if $sMenuItem=='index'}active{/if}">
            {$aLang.action.admin.dashboard_main}
        </a>
        {hook run='admin_dashboard_left_items'}
    </div>
    <div class="btn-group pull-right">
        {hook run='admin_dashboard_right_items'}
    </div>
{/block}

{block name="content-body"}

        {if $aDashboardWidgets.admin_dashboard_updates.status}
            <div class="span6" id="admin-dashboard-updates">
                <div class="b-wbox b-wbox-console">
                    <div class="b-wbox-header">
                        <button type="button" class="close tip-top" title="{$aLang.action.admin.content_turn_off}" onclick="return ls.dashboard.updatesOff();">×</button>
                        <h3 class="b-wbox-header-title">{$aLang.action.admin.dashboard_updates_title}</h3>
                    </div>
                    <div class="b-wbox-content b-dashboard-updates {if $sUpdatesRefresh}refresh{/if}">
                    </div>
                </div>
            </div>
        {/if}
        {if $aDashboardWidgets.admin_dashboard_news.status}
        <div class="span6" id="admin-dashboard-news">
            <div class="b-wbox b-wbox-console">
                <div class="b-wbox-header">
                    <button type="button" class="close tip-top" title="{$aLang.action.admin.content_turn_off}" onclick="return ls.dashboard.newsOff();">×</button>
                    <h3 class="b-wbox-header-title">{$aLang.action.admin.dashboard_news_title}</h3>
                </div>
                <div class="b-wbox-content b-dashboard-news {if $sUpdatesRefresh}refresh{/if}">
                </div>
            </div>
        </div>
        {/if}
        {hook run='admin_info_index_box'}

        {if $sUpdatesRefresh}
        <script>
            jQuery(function(){
                admin.dashboardNews('');
                admin.dashboardUpdates('{$sUpdatesRequest}');
            });
        </script>
        {/if}

    <div class="span12">
        <div class="b-wbox">
            <div class="b-wbox-content">
                {$aLang.action.admin.dashboard_add_widget}
            </div>
        </div>
    </div>

    <div class="modal uniform" id="dashboard_add_form">
        <header class="modal-header">
            <button type="button" class="close jqmClose">&times;</button>
            <h3 class="modal-title">{$aLang.action.admin.widgets_title}</h3>
        </header>

        <form method="post" action="">
            <div class="modal-content">
                <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
                <input type="hidden" name="widgets[]" value="" />

                {foreach $aDashboardWidgets as $aWidget}
                    <p>
                        <label>
                            <input type="checkbox" name="widgets[]" value="{$aWidget.name}" {if $aWidget.status}checked{/if} />
                            {$aWidget.label}
                        </label>
                    </p>
                {/foreach}
            </div>

            <footer class="modal-footer">
                <input type="submit" class="btn btn-primary" value="{$aLang.action.admin.save}" />
            </footer>
        </form>
    </div>

    <script>
        var ls = ls || { };

        ls.dashboard = {
            widgetOff: function(widgetId, widgetKey) {
                var params = { };
                params[widgetKey] = false;
                ls.ajaxConfig(params, function(){
                    $(widgetId).animate({ width: 0, opacity: 0 }, function(){
                        $(this).remove();
                    });
                });
            },
            newsOff: function() {
                ls.dashboard.widgetOff('#admin-dashboard-news', 'admin.dashboard.news');
                return false;
            },
            updatesOff: function() {
                ls.dashboard.widgetOff('#admin-dashboard-updates', 'admin.dashboard.updates');
                return false;
            },
            showAddForm: function() {
                $('#dashboard_add_form').jqmShow();
            }
        };

        $(function(){
            $('#dashboard_add_form').jqm();
        });
    </script>

{/block}