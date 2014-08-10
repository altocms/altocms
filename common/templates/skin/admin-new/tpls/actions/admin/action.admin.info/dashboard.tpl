{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="#" class="btn btn-primary pull-right" onclick="ls.dashboard.showAddForm(); return false;"
    title="{$aLang.action.admin.user_field_add}"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills atlass">
    <li class=" {if $sMenuItem=='index'}active{/if}">
      <a href="{router page='admin'}info-dashboard/">
      {$aLang.action.admin.dashboard_main}
      </a>
    </li>
    {hook run='admin_dashboard_left_items'}
    {hook run='admin_dashboard_right_items'}
  </ul>
</div>
{/block}
{block name="content-body"}
{if $aDashboardWidgets.admin_dashboard_updates.status}
<div class="col-md-6" id="admin-dashboard-updates">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="tools pull-right">
        <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="tooltip" title data-original-title="{$aLang.action.admin.delete}" onclick="return ls.dashboard.updatesOff();"><i class="glyphicon glyphicon-remove"></i></button>
        <button style="margin-right: 5px;" class="btn btn-primary btn-sm pull-right" data-widget="collapse" data-toggle="tooltip" title data-original-title="{$aLang.action.admin.collapse}"><i class="glyphicon glyphicon-minus"></i></button>
      </div>
      <h3 class="panel-title">{$aLang.action.admin.dashboard_updates_title}</h3>
    </div>
    <div class="panel-body b-dashboard-updates {if $sUpdatesRefresh}refresh{/if}">
    </div>
  </div>
</div>
{/if}
{if $aDashboardWidgets.admin_dashboard_news.status}
<div class="col-md-6" id="admin-dashboard-news">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="tools pull-right">
        <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="tooltip" title data-original-title="{$aLang.action.admin.delete}" onclick="return ls.dashboard.newsOff();"><i class="glyphicon glyphicon-remove"></i></button>
        <button style="margin-right: 5px;" class="btn btn-primary btn-sm pull-right" data-widget="collapse" data-toggle="tooltip" title data-original-title="{$aLang.action.admin.collapse}"><i class="glyphicon glyphicon-minus"></i></button>
      </div>
      <h3 class="panel-title">{$aLang.action.admin.dashboard_news_title}</h3>
    </div>
    <div class="panel-body b-dashboard-news {if $sUpdatesRefresh}refresh{/if}">
    </div>
  </div>
</div>
{/if}
{hook run='admin_info_index_box'}
{if $sUpdatesRefresh}
<script>
  jQuery(function () {
      admin.dashboardNews('');
      admin.dashboardUpdates('{$sUpdatesRequest}');
  });
</script>
{/if}
<div class="col-md-12">
  <div class="panel panel-default noborder">
    <div class="panel-body">
      {$aLang.action.admin.dashboard_add_widget}
    </div>
  </div>
</div>
<!-- modal -->
<div class="modal fade in" id="modal-dashboard_add_widgets">
  <div class="modal-dialog">
    <div class="modal-content">
      <header class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 class="modal-title">{$aLang.action.admin.widgets_title}</h3>
      </header>
      <form method="post" action="">
        <div class="modal-body">
          <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
          <input type="hidden" name="widgets[]" value=""/>
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
          <input type="submit" class="btn btn-primary" value="{$aLang.action.admin.save}"/>
        </footer>
      </form>
    </div>
  </div>
</div>
<!-- /modal -->
    <script>
        var ls = ls || { };

        ls.dashboard = {
            widgetOff: function (widgetId, widgetKey, widgetName) {
                ls.progressStart();
                var params = { };
                params[widgetKey] = false;console.log(params);
                ls.ajaxConfig(params, function () {
                    $(widgetId).animate({ height: '1px' }, function () {
                        $(this).animate({ width: 0, opacity: 0 }, function () {
                            $(this).remove();
                            ls.progressDone();
                        });
                    });
                    $('[value=' + widgetName + ']')
                            .prop('checked', false)
                            .parents('[class=checked]')
                            .first()
                            .removeClass('checked');
                });
            },
            newsOff: function () {
                ls.dashboard.widgetOff('#admin-dashboard-news', 'admin.dashboard.news', 'admin_dashboard_news');
                return false;
            },
            updatesOff: function () {
                ls.dashboard.widgetOff('#admin-dashboard-updates', 'admin.dashboard.updates', 'admin_dashboard_updates');
                return false;
            },
            showAddForm: function () {
                $('#modal-dashboard_add_widgets').modal('show');
            }
        };

    </script>
{/block}