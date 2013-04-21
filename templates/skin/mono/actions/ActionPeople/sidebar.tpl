{hook run='people_sidebar_begin'}
<section class="b-widget">
	<header class="b-widget-header">
		{$aLang.user_stats}
	</header>
	
	
	<div class="b-widget-content">
		<ul>
			<li>{$aLang.user_stats_all}: <strong>{$aStat.count_all}</strong></li>
			<li>{$aLang.user_stats_active}: <strong>{$aStat.count_active}</strong></li>
			<li>{$aLang.user_stats_noactive}: <strong>{$aStat.count_inactive}</strong></li>
		</ul>
		
		<br />
		
		<ul>
			<li>{$aLang.user_stats_sex_man}: <strong>{$aStat.count_sex_man}</strong></li>
			<li>{$aLang.user_stats_sex_woman}: <strong>{$aStat.count_sex_woman}</strong></li>
			<li>{$aLang.user_stats_sex_other}: <strong>{$aStat.count_sex_other}</strong></li>
		</ul>
	</div>
</section>


{widget name="tagsCountry"}
{widget name="tagsCity"}

{hook run='people_sidebar_end'}