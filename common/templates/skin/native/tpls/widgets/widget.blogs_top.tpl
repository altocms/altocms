{**
 * Блок со списоком блогов
 * Список блогов
 *
 * @styles css/widgets.css
 *}

<ul class="item-list">
	{foreach $aBlogs as $oBlog}
		<li>
			<a href="{$oBlog->getUrlFull()}" class="blog-name">{$oBlog->getTitle()|escape:'html'}</a> {if $oBlog->getType()=='close'}<i title="{$aLang.blog_closed}" class="icon icon-lock"></i>{/if} <span class="block-count" title="{$aLang.blog_rating}">{$oBlog->getRating()}</span>
		</li>
	{/foreach}
</ul>				