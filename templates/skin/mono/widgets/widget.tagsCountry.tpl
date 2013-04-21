{if $aCountryList && count($aCountryList)>0}
	<section class="b-widget">
		<header class="b-widget-header">
			{$aLang.block_country_tags}
		</header>
		
		
		<div class="b-widget-content">
			<ul class="tag-cloud word-wrap">
				{foreach from=$aCountryList item=oCountry}
					<li><a class="tag-size-{$oCountry->getSize()}" href="{router page='people'}country/{$oCountry->getId()}/">{$oCountry->getName()|escape:'html'}</a></li>
				{/foreach}					
			</ul>	
		</div>		
	</section>
{/if}