<section class="panel panel-default widget widget-people-stats">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.user_stats}</h3>
        </header>

        <div class="widget-content">
            <ul class="list-unstyled">
                <li>{$aLang.user_stats_all}: <strong>{$aPeopleStats.count_all}</strong></li>
                <li>{$aLang.user_stats_active}: <strong>{$aPeopleStats.count_active}</strong></li>
                <li>{$aLang.user_stats_noactive}: <strong>{$aPeopleStats.count_inactive}</strong></li>
            </ul>

            <br/>

            <ul class="list-unstyled">
                <li>{$aLang.user_stats_sex_man}: <strong>{$aPeopleStats.count_sex_man}</strong></li>
                <li>{$aLang.user_stats_sex_woman}: <strong>{$aPeopleStats.count_sex_woman}</strong></li>
                <li>{$aLang.user_stats_sex_other}: <strong>{$aPeopleStats.count_sex_other}</strong></li>
            </ul>
        </div>

    </div>
</section>
