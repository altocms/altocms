<div class="b-widget">
	<header class="b-widget-header">
		{$aLang.plugin.categories.categories}
	</header>
	
	<div class="b-widget-content">
		<ul class="block-blog-list">
			{foreach from=$aCategories item=oCategory}
				<li>
					{strip}
						<a {if $sEvent==$oCategory->getCategoryUrl()}class="category-active"{/if} href="{$oCategory->getUrl()}">{$oCategory->getCategoryTitle()|escape:'html'}</a>
					{/strip}
				</li>
			{/foreach}
		</ul>
	</div>
</div>
