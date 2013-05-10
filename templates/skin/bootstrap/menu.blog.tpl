<ul class="nav nav-pills">
    <li {if $sMenuItemSelect=='index' && $sMenuSubItemSelect=='good'}class="active"{/if}>
        <a href="{cfg name='path.root.web'}">{$aLang.blog_menu_all}</a>
    </li>

    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
        <a href="{router page='index'}newall/">{$aLang.blog_menu_all_new}</a>
        {*if $iCountTopicsNew>0}<a href="{router page='index'}new/" class="new" title="{$aLang.blog_menu_top_period_24h}">+{$iCountTopicsNew}</a>{/if*}
    </li>

    {if $oUserCurrent}
        <li {if $sMenuItemSelect=='feed'}class="active"{/if}>
            <a href="{router page='feed'}">{$aLang.userfeed_title}</a>
        </li>
    {/if}

    {hook run='menu_blog'}
</ul>

{*For desktop
<div class="nav-filter-wrapper">
	<ul>
		<li class="btn-group {if $sMenuItemSelect=='index'}active{/if}">
			<a href="{cfg name='path.root.web'}/" class="btn">{$aLang.blog_menu_all} {if $iCountTopicsNew>0}+{$iCountTopicsNew}{/if}</a>
            <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{cfg name='path.root.web'}/">{$aLang.blog_menu_all_good}</a></li>
                {if $iCountTopicsNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='index'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_all_new} +{$iCountTopicsNew}</a>
                    </li>
                {/if}
                <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='index'}discussed/">{$aLang.blog_menu_all_discussed}</a></li>
                <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='index'}top/">{$aLang.blog_menu_all_top}</a></li>
                {hook run='menu_blog_index_item'}
            </ul>
		</li>

		<li class="btn-group {if $sMenuItemSelect=='blog'}active{/if}">
			<a href="{router page='blog'}" class="btn">{$aLang.blog_menu_collective} {if $iCountTopicsCollectiveNew>0}+{$iCountTopicsCollectiveNew}{/if}</a>
            <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{router page='blog'}">{$aLang.blog_menu_collective_good}</a></li>
                {if $iCountTopicsBlogNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='blog'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_collective_new} +{$iCountTopicsBlogNew}</a>
                    </li>
                {/if}
                <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='blog'}discussed/">{$aLang.blog_menu_collective_discussed}</a></li>
                <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='blog'}top/">{$aLang.blog_menu_collective_top}</a></li>
                {hook run='menu_blog_blog_item'}
            </ul>
		</li>

		<li class="btn-group {if $sMenuItemSelect=='log'}active{/if}">
			<a href="{router page='personal_blog'}" class="btn">{$aLang.blog_menu_personal} {if $iCountTopicsPersonalNew>0}+{$iCountTopicsPersonalNew}{/if}</a>
            <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{router page='personal_blog'}">{$aLang.blog_menu_personal_good}</a></li>
                {if $iCountTopicsPersonalNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='personal_blog'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_personal_new} +{$iCountTopicsPersonalNew}</a>
                    </li>
                {/if}
                <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='personal_blog'}discussed/">{$aLang.blog_menu_personal_discussed}</a></li>
                <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='personal_blog'}top/">{$aLang.blog_menu_personal_top}</a></li>
            {hook run='menu_blog_log_item'}
            </ul>
		</li>
		
		{if $oUserCurrent}
			<li class="my-userfeed btn-group {if $sMenuItemSelect=='feed'}active{/if}">
				<a href="{router page='feed'}" class="btn btn-info">{$aLang.userfeed_title}</a>
                <a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li {if $sPeriodSelectCurrent=='1'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                    <li {if $sPeriodSelectCurrent=='7'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                    <li {if $sPeriodSelectCurrent=='30'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                    <li {if $sPeriodSelectCurrent=='all'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=all">{$aLang.blog_menu_top_period_all}</a></li>
                </ul>
			</li>
		{/if}

		{hook run='menu_blog'}
	</ul>
</div>

{*For tablet & phone
<div class="nav-filter-wrapper filters-container">
    <div class="filters-box">
        <ul class="filters">
            <li class="btn-group {if $sMenuItemSelect=='index'}active{/if}">
                <a href="{cfg name='path.root.web'}/" class="btn">{$aLang.blog_menu_all} {if $iCountTopicsNew>0}+{$iCountTopicsNew}{/if}</a>
                <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{cfg name='path.root.web'}/">{$aLang.blog_menu_all_good}</a></li>
                {if $iCountTopicsNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='index'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_all_new} +{$iCountTopicsNew}</a>
                    </li>
                {/if}
                    <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='index'}discussed/">{$aLang.blog_menu_all_discussed}</a></li>
                    <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='index'}top/">{$aLang.blog_menu_all_top}</a></li>
                {hook run='menu_blog_index_item'}
                </ul>
            </li>

            <li class="btn-group {if $sMenuItemSelect=='blog'}active{/if}">
                <a href="{router page='blog'}" class="btn">{$aLang.blog_menu_collective} {if $iCountTopicsCollectiveNew>0}+{$iCountTopicsCollectiveNew}{/if}</a>
                <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{router page='blog'}">{$aLang.blog_menu_collective_good}</a></li>
                {if $iCountTopicsBlogNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='blog'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_collective_new} +{$iCountTopicsBlogNew}</a>
                    </li>
                {/if}
                    <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='blog'}discussed/">{$aLang.blog_menu_collective_discussed}</a></li>
                    <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='blog'}top/">{$aLang.blog_menu_collective_top}</a></li>
                {hook run='menu_blog_blog_item'}
                </ul>
            </li>

            <li class="btn-group {if $sMenuItemSelect=='log'}active{/if}">
                <a href="{router page='personal_blog'}" class="btn">{$aLang.blog_menu_personal} {if $iCountTopicsPersonalNew>0}+{$iCountTopicsPersonalNew}{/if}</a>
                <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{router page='personal_blog'}">{$aLang.blog_menu_personal_good}</a></li>
                {if $iCountTopicsPersonalNew>0}
                    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
                        <a href="{router page='personal_blog'}new/" title="{$aLang.blog_menu_top_period_24h}">{$aLang.blog_menu_personal_new} +{$iCountTopicsPersonalNew}</a>
                    </li>
                {/if}
                    <li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='personal_blog'}discussed/">{$aLang.blog_menu_personal_discussed}</a></li>
                    <li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='personal_blog'}top/">{$aLang.blog_menu_personal_top}</a></li>
                {hook run='menu_blog_log_item'}
                </ul>
            </li>

        {if $oUserCurrent}
            <li class="my-userfeed btn-group {if $sMenuItemSelect=='feed'}active{/if}">
                <a href="{router page='feed'}" class="btn btn-info">{$aLang.userfeed_title}</a>
                <a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li {if $sPeriodSelectCurrent=='1'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                    <li {if $sPeriodSelectCurrent=='7'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                    <li {if $sPeriodSelectCurrent=='30'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                    <li {if $sPeriodSelectCurrent=='all'}class="active"{/if}><a href="{$sPeriodSelectRoot}?period=all">{$aLang.blog_menu_top_period_all}</a></li>
                </ul>
            </li>
        {/if}

        {hook run='menu_blog'}
        </ul>
    </div>
</div>
<div class="mb-50 hidden-desktop" id="margin-filter"></div>*}