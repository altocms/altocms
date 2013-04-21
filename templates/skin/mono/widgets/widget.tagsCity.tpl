{if $aCityList && count($aCityList)>0}
	<section class="b-widget">
		<header class="b-widget-header">
			{$aLang.block_city_tags}
		</header>
		
		
		<div class="b-widget-content">
			<ul class="tag-cloud word-wrap">
				{foreach from=$aCityList item=oCity}
					<li><a class="tag-size-{$oCity->getSize()}" href="{router page='people'}city/{$oCity->getId()}/">{$oCity->getName()|escape:'html'}</a></li>
				{/foreach}					
			</ul>	
		</div>		
	</section>
{/if}