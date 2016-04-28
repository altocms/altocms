<div class="page-header">
	<div class=" header">{$aLang.blog_admin}: <a href="{$oBlogEdit->getUrlFull()}">{$oBlogEdit->getTitle()|escape:'html'}</a></div>
</div>

<ul class="nav nav-pills nav-filter-wrapper">
	<li {if $sMenuItemSelect=='profile'}class="active"{/if}><a href="{R::GetLink("blog")}edit/{$oBlogEdit->getId()}/">{$aLang.blog_admin_profile}</a></li>
	<li {if $sMenuItemSelect=='admin'}class="active"{/if}><a href="{R::GetLink("blog")}admin/{$oBlogEdit->getId()}/">{$aLang.blog_admin_users}</a></li>

	{hook run='menu_blog_edit_admin_item'}
</ul>

{hook run='menu_blog_edit'}
