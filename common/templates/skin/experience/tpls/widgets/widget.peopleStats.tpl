<div class="panel panel-default panel-statistic sidebar raised widget widget-people-stats">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-bar-chart-o"></i>{$aLang.user_stats}
        </div>

        <div class="panel-content">
            <ul class="marked-list no-images">
                <li class="user-block color-50">
                    <span>{$aLang.user_stats_all}:</span><span class="strong">{$aPeopleStats.count_all}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_active}:</span><span class="strong">{$aPeopleStats.count_active}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_noactive}:</span><span class="strong">{$aPeopleStats.count_inactive}</span>
                </li>
            </ul>
            <hr/>
            <ul class="marked-list no-images">
                <li class="user-block color-50">
                    <span>{$aLang.user_stats_sex_man}:</span><span class="strong">{$aPeopleStats.count_sex_man}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_sex_woman}:</span><span class="strong">{$aPeopleStats.count_sex_woman}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_sex_other}:</span><span class="strong">{$aPeopleStats.count_sex_other}</span>
                </li>
            </ul>
        </div>

    </div>
    <div class="panel-footer">
        <ul>
            <li>&nbsp;</li>
        </ul>
    </div>
</div>
