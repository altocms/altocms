{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12 mb15">
  <a href="{router page='admin'}site-widgets/" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i></a>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  <form method="post" action="" class="form-horizontal">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">
          {if $oWidget->GetTitle()}
          {$oWidget->GetTitle()} - {$oWidget->GetName()}
          {else}
          {$oWidget->GetName()}
          {/if}
        </h3>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_name}</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{$oWidget->GetName()}" readonly="readonly">
          </div>
        </div>
        {if $oWidget->GetPluginId()}
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_plugin}</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{$oWidget->GetPluginId()}" readonly="readonly">
          </div>
        </div>
        {/if}
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_group}</label>
          <div class="col-sm-10">
            <input class="form-control" type="text" name="widget_group" value="{$oWidget->GetGroup()}" >
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_priority}</label>
          <div class="col-sm-10">
            <span class="input-group pull-left">
            <input class="form-control" type="text" name="widget_priority" value="{$oWidget->GetPriority()}" />
            <span class="input-group-addon"><i class="ion-arrow-graph-up-right"></i></span>
            </span>
            <!-- label class="offset3">
              <input type="checkbox" class="input-checkbox" value="{$oWidget->GetPriority()}" >
              TOP
              </label -->
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_display}</label>
          <div class="col-sm-10">
            <label>
            <input class="form-control" type="radio" name="widget_display" value="always" {if !$oWidget->GetPeriod()}checked="checked"{/if}>
            {$aLang.action.admin.widget_display_alwyas}
            </label>
              <input class="form-control" type="radio" name="widget_display" value="period" {if $oWidget->GetPeriod()}checked="checked"{/if}/>
            <label for="widget_display">
              {$aLang.action.admin.widget_display_period}
              {$aLang.action.admin.widget_display_from}</label>
                <span class="input-group">
                <input type="text" name="widget_period_from" value="{$oWidget->GetDateFrom()}" class="datepicker form-control" />
                <span class="input-group-addon"><i class="ion-ios7-calendar"></i></span>
                </span>
                {$aLang.action.admin.widget_display_upto}
                <span class="input-group">
                <input type="text" name="widget_period_upto" value="{$oWidget->GetDateUpto()}" class="datepicker form-control" />
                <span class="input-group-addon"><i class="ion-ios7-calendar"></i></span>
                </span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_showto}</label>
          <div class="col-sm-10">
            <select class="form-control" name="widget_visitors">
              <option value="all" {if $oWidget->GetVisitors()==''}selected="selected"{/if}>
                {$aLang.action.admin.widget_showto_all}
              </option>
              <option value="users" {if $oWidget->GetVisitors()=='users'}selected="selected"{/if}>
                {$aLang.action.admin.widget_showto_users}
              </option>
              <option value="admins" {if $oWidget->GetVisitors()=='admins'}selected="selected"{/if}>
                {$aLang.action.admin.widget_showto_admins}
              </option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.widget_active}</label>
          <div class="col-sm-10">
            <label>
            <input class="form-control" type="radio" name="widget_active" value="1" {if $oWidget->isActive()}checked="checked"{/if}> {$aLang.action.admin.word_yes}
            </label>
            <label>
            <input class="form-control" type="radio" name="widget_active" value="0" {if !$oWidget->isActive()}checked="checked"{/if}> {$aLang.action.admin.word_no}
            </label>
          </div>
        </div>
      </div>
      <div class="panel-footer clearfix">
        <button type="submit" name="submit_widget" class="btn btn-primary pull-right">
        {$aLang.action.admin.save}
        </button>
      </div>
    </div>
  </form>
</div>
{/block}