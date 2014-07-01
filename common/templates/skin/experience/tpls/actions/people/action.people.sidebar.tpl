 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}


{hook run='people_sidebar_begin'}

<div class="panel panel-default panel-statistic sidebar raised widget">
    <div class="panel-body">
        <h4 class="panel-header">
            <i class="fa fa-bar-chart-o"></i>{$aLang.user_stats}
        </h4>

        <div class="panel-content">
            <ul class="marked-list no-images">
                <li class="user-block color-50">
                    <span>{$aLang.user_stats_all}</span><span class="strong">{$aStat.count_all}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_active}</span><span class="strong">{$aStat.count_active}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_noactive}</span><span class="strong">{$aStat.count_inactive}</span>
                </li>
            </ul>
            <hr/>
            <ul class="marked-list no-images">
                <li class="user-block color-50">
                    <span>{$aLang.user_stats_sex_man}</span><span class="strong">{$aStat.count_sex_man}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_sex_woman}</span><span class="strong">{$aStat.count_sex_woman}</span>
                </li>
                <li class="date-block color-50">
                    <span>{$aLang.user_stats_sex_other}</span><span class="strong">{$aStat.count_sex_other}</span>
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

{widget name='tagsCountry'}
{widget name='tagsCity'}

{hook run='people_sidebar_end'}
