<div class="block block-type-blogs">
	<header class="block-header sep">
		<h3>{$aLang.plugin.categories.categories}</h3>
	</header>
	
	<div class="block-content">
		<div class="js-block-blogs-content">
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
</div>
