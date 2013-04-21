<script type="text/javascript">
	jQuery(function($){
		var trigger = $('#dropdown-create-trigger');
		var menu 	= $('#dropdown-create-menu');
		var pos 	= trigger.position();
	
		// Dropdown
		menu.css({ 'left': pos.left - 5 });
	
		trigger.click(function(){
			menu.slideToggle(); 
			return false;
		});
		
		
		// Hide menu
		$(document).click(function(){
			menu.slideUp();
		});
	
		$('body').on("click", "#dropdown-create-trigger, #dropdown-create-menu", function(e) {
			e.stopPropagation();
		});
	});
</script>

<div class="dropdown-create">
	<h2 class="page-header">{$aLang.block_create} <a href="#" class="dropdown-create-trigger link-dashed" id="dropdown-create-trigger">
			{if $sAction=='content'}
				{foreach from=$aContentTypes item=oType}
					{if $sEvent==$oType->getContentUrl()}{$oType->getContentTitle()|escape:'html'}{/if}
				{/foreach}
			{elseif $sMenuItemSelect=='blog'}
				{$aLang.blog_menu_create}
			{elseif $sMenuItemSelect=='talk'}
				{$aLang.block_create_talk}
			{else}
				{hook run='menu_create_item_select' sMenuItemSelect=$sMenuItemSelect}
			{/if}
		</a></h2>
	{/strip}

	<ul class="dropdown-menu-create" id="dropdown-create-menu" style="display: none">
		{foreach from=$aContentTypes item=oType}
			<li {if $sEvent==$oType->getContentUrl()}class="active"{/if}><a href="{router page='content'}{$oType->getContentUrl()}/add/">{$oType->getContentTitle()|escape:'html'}</a></li>
		{/foreach}
		<li {if $sMenuItemSelect=='blog'}class="active"{/if}><a href="{router page='blog'}add/">{$aLang.blog_menu_create}</a></li>
		<li {if $sMenuItemSelect=='talk'}class="active"{/if}><a href="{router page='talk'}add/">{$aLang.block_create_talk}</a></li>
		{hook run='menu_create_item' sMenuItemSelect=$sMenuItemSelect}
	</ul>
</div>

{if $sMenuItemSelect=='topic'}
	{if $iUserCurrentCountTopicDraft}
		<a href="{router page='topic'}saved/" class="drafts">{$aLang.topic_menu_saved} ({$iUserCurrentCountTopicDraft})</a>
	{/if}
{/if}

{hook run='menu_create' sMenuItemSelect=$sMenuItemSelect sMenuSubItemSelect=$sMenuSubItemSelect}