<div class="page-header">
	<h1>{$aLang.blog_admin}: <a href="{$oBlogEdit->getUrlFull()}">{$oBlogEdit->getTitle()|escape:'html'}</a></h1>
</div>

<ul class="nav nav-pills nav-filter-wrapper">
	<li {if $sMenuItemSelect=='profile'}class="active"{/if}><a href="{router page='blog'}edit/{$oBlogEdit->getId()}/">{$aLang.blog_admin_profile}</a></li>
	<li {if $sMenuItemSelect=='admin'}class="active"{/if}><a href="{router page='blog'}admin/{$oBlogEdit->getId()}/">{$aLang.blog_admin_users}</a></li>

	{hook run='menu_blog_edit_admin_item'}
</ul>

{hook run='menu_blog_edit'}
