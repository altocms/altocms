{extends file='_index.tpl'}

{block name="content-body"}

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

{/block}