{**
 * Навигация по топикам
 *}

<ul class="nav nav-pills">
	<li {if $sMenuItemSelect=='index'}class="active"{/if}>
		<a href="{cfg name='path.root.web'}">{$aLang.blog_menu_all}</a> {if $iCountTopicsNew}<span class="nav-count">{$iCountTopicsNew}</span>{/if}
	</li>
    
    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
        <a href="{router page='index'}newall/">{$aLang.blog_menu_all_new}</a>
	</li>
	
	{if $oUserCurrent}
		<li {if $sMenuItemSelect=='feed'}class="active"{/if}>
			<a href="{router page='feed'}">{$aLang.userfeed_title}</a>
		</li>
	{/if}

	{hook run='menu_blog'}
</ul>



{*if $sMenuItemSelect == 'index'}
	<ul class="nav nav-pills fl-l">
		<li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{cfg name='path.root.web'}/">{$aLang.blog_menu_all_good}</a></li>
		<li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
			<a href="{router page='index'}newall/" title="{$aLang.blog_menu_top_period_all}">{$aLang.blog_menu_all_new}</a>
			{if $iCountTopicsNew} <a href="{router page='index'}new/" title="{$aLang.blog_menu_top_period_24h}">+{$iCountTopicsNew}</a>{/if}
		</li>
		<li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{router page='index'}discussed/">{$aLang.blog_menu_all_discussed}</a></li>
		<li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{router page='index'}top/">{$aLang.blog_menu_all_top}</a></li>
		{hook run='menu_blog_index_item'}
	</ul>
{/if}

{if $sMenuItemSelect == 'blog'}
	<ul class="nav nav-pills fl-l">
		<li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{$sMenuSubBlogUrl}">{$aLang.blog_menu_collective_good}</a></li>
		<li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
			<a href="{$sMenuSubBlogUrl}newall/" title="{$aLang.blog_menu_top_period_all}">{$aLang.blog_menu_collective_new}</a>
			{if $iCountTopicsBlogNew} <a href="{$sMenuSubBlogUrl}new/" title="{$aLang.blog_menu_top_period_24h}">+{$iCountTopicsBlogNew}</a>{/if}
		</li>
		<li {if $sMenuSubItemSelect=='discussed'}class="active"{/if}><a href="{$sMenuSubBlogUrl}discussed/">{$aLang.blog_menu_collective_discussed}</a></li>
		<li {if $sMenuSubItemSelect=='top'}class="active"{/if}><a href="{$sMenuSubBlogUrl}top/">{$aLang.blog_menu_collective_top}</a></li>

		{hook run='menu_blog_blog_item'}
	</ul>
{/if*}

{if $sMenuItemSelect == 'feed'}
	<ul class="nav nav-pills fl-l">
        <li {if $sMenuSubItemSelect=='feed'}class="active"{/if}><a href="{router page='feed'}">{$aLang.subscribe_menu}</a></li>
        <li {if $sMenuSubItemSelect=='track'}class="active"{/if}><a href="{router page='feed'}track/">{$aLang.subscribe_tracking_menu}</a></li>
        {if $iUserCurrentCountTrack}
            <li {if $sMenuSubItemSelect=='track_new'}class="active"{/if}><a href="{router page='feed'}track/new/">{$aLang.subscribe_tracking_menu_new} <span class="block-count">+{$iUserCurrentCountTrack}</span></a></li>
        {/if}
	</ul>
{/if}

{include file='dropdown.timespan.tpl'}