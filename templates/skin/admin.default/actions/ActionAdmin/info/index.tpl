{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}dasboard/index/" class="btn {if $sMenuItem=='index'}active{/if}">
            {$aLang.action.admin.dashboard_main}
        </a>
    </div>
    {hook run='admin_dashboard_index_box'}
{/block}

{block name="content-body"}

    {if $bDashboardEnable}
<div class="span6">
    <div class="b-wbox">
        <div class="b-wbox-header">
            <h3 class="b-wbox-header-title">{$aLang.action.admin.dashboard_updates_title}</h3>
        </div>
        <div class="b-wbox-content b-dashboard-updates {if $sUpdatesRefresh}refresh{/if}">
        </div>
    </div>
</div>

<div class="span6">
    <div class="b-wbox">
        <div class="b-wbox-header">
            <h3 class="b-wbox-header-title">{$aLang.action.admin.dashboard_news_title}</h3>
        </div>
        <div class="b-wbox-content b-dashboard-info {if $sUpdatesRefresh}refresh{/if}">
        </div>
    </div>
</div>

{hook run='admin_info_index_box'}

{if $sUpdatesRefresh}
<script>
    jQuery(function(){
        admin.dashboardInfo('');
        admin.dashboardUpdates('{$sUpdatesRequest}');
    });
</script>
{/if}
{/if}

    <div class="span12">
        <div class="b-wbox">
            <div class="b-wbox-header">
                <h3 class="b-wbox-header-title">{$aLang.action.admin.dashboard_turn_title}</h3>
            </div>
            <div class="b-wbox-content">
                {if $bDashboardEnable}
                    {$aLang.action.admin.dashboard_turn_on_text}
                {else}
                    {$aLang.action.admin.dashboard_turn_off_text}
                {/if}
            </div>
        </div>

        <form action="" method="post" class="uniform">
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
            <div class="navbar navbar-inner">
                {if $bDashboardEnable}
                    <input type="hidden" name="dashboard_enable" value="off"/>
                    <input type="submit" value="{$aLang.action.admin.turn_off}"
                           class="btn btn-danger pull-right"/>
                {else}
                    <input type="hidden" name="dashboard_enable" value="on"/>
                    <input type="submit" value="{$aLang.action.admin.turn_on}"
                           class="btn btn-primary pull-right"/>
                {/if}
            </div>
        </form>
    </div>

{/block}