{hook run='people_sidebar_begin'}

{widget name='tagsCity'}
{widget name='tagsCountry'}

<section class="panel panel-default widget">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.user_stats}</h3>
        </header>

        <div class="widget-content">
            <ul class="list-unstyled">
                <li>{$aLang.user_stats_all}: <strong>{$aStat.count_all}</strong></li>
                <li>{$aLang.user_stats_active}: <strong>{$aStat.count_active}</strong></li>
                <li>{$aLang.user_stats_noactive}: <strong>{$aStat.count_inactive}</strong></li>
            </ul>

            <br/>

            <ul class="list-unstyled">
                <li>{$aLang.user_stats_sex_man}: <strong>{$aStat.count_sex_man}</strong></li>
                <li>{$aLang.user_stats_sex_woman}: <strong>{$aStat.count_sex_woman}</strong></li>
                <li>{$aLang.user_stats_sex_other}: <strong>{$aStat.count_sex_other}</strong></li>
            </ul>
        </div>

    </div>
</section>

{hook run='people_sidebar_end'}
