{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}widgets/" class="btn"><i class="icon-chevron-left"></i></a>
    </div>
{/block}

{block name="content-body"}
    <div class="span12">
        <form method="post" action="" class="form-horizontal uniform">
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

            <div class="b-wbox">
                <div class="b-wbox-header">
                    <div class="b-wbox-header-title">
                    {if $oWidget->GetTitle()}
                        {$oWidget->GetTitle()} - {$oWidget->GetName()}
                    {else}
                        {$oWidget->GetName()}
                    {/if}
                    </div>
                </div>
                <div class="b-wbox-content nopadding">
                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_name}</label>

                        <div class="controls">
                            <input type="text" class="input-text" value="{$oWidget->GetName()}" readonly="readonly">
                        </div>
                    </div>

                    {if $oWidget->GetPluginId()}
                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_plugin}</label>

                        <div class="controls">
                            <input type="text" class="input-text" value="{$oWidget->GetPluginId()}" readonly="readonly">
                        </div>
                    </div>
                    {/if}

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_group}</label>

                        <div class="controls">
                            <input type="text" name="widget_group" value="{$oWidget->GetGroup()}" >
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_priority}</label>

                        <div class="controls">
                            <span class="input-append pull-left">
                                <input type="text" name="widget_priority" value="{$oWidget->GetPriority()}" />
                                <span class="add-on"><i class="icon-circle-arrow-up"></i></span>
                            </span>
                            <!-- label class="offset3">
                                <input type="checkbox" class="input-checkbox" value="{$oWidget->GetPriority()}" >
                                TOP
                            </label -->
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_display}</label>

                        <div class="controls">
                            <label>
                                <input type="radio" name="widget_display" value="always" {if !$oWidget->GetPeriod()}checked="checked"{/if}>
                                {$aLang.action.admin.widget_display_alwyas}
                            </label>
                            <label>
                                <input type="radio" name="widget_display" value="period" {if $oWidget->GetPeriod()}checked="checked"{/if}>
                                {$aLang.action.admin.widget_display_period}
                                {$aLang.action.admin.widget_display_from}
                                <span class="input-append">
                                    <input type="text" name="widget_period_from" value="{$oWidget->GetDateFrom()}" class="datepicker" />
                                    <span class="add-on"><i class="icon-calendar"></i></span>
                                </span>
                                {$aLang.action.admin.widget_display_upto}
                                <span class="input-append">
                                    <input type="text" name="widget_period_upto" value="{$oWidget->GetDateUpto()}" class="datepicker" />
                                    <span class="add-on"><i class="icon-calendar"></i></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_showto}</label>

                        <div class="controls">
                            <select name="widget_visitors">
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

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.widget_active}</label>

                        <div class="controls">
                            <label>
                                <input type="radio" name="widget_active" value="1" {if $oWidget->isActive()}checked="checked"{/if}> {$aLang.action.admin.word_yes}
                            </label>
                            <label>
                                <input type="radio" name="widget_active" value="0" {if !$oWidget->isActive()}checked="checked"{/if}> {$aLang.action.admin.word_no}
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="navbar navbar-inner">
                <button type="submit" name="submit_widget" class="btn btn-primary pull-right"">
                    {$aLang.action.admin.save}
                </button>
            </div>

        </form>
    </div>
{/block}