{include file='header.tpl' sMenuHeadItemSelect="blogs"}

<h2 class="page-header">{$aLang.blogs}</h2>

<ul class="nav nav-pills">
	<li {if $sShow=='collective'}class="active"{/if}><a href="{router page='blogs'}">{$aLang.blog_menu_collective}</a></li>
	<li {if $sShow=='personal'}class="active"{/if}><a href="{router page='blogs'}personal/">{$aLang.blog_menu_personal}</a></li>
	{hook run='blog_list_nav_menu'}
</ul>


<form action="" method="POST" id="form-blogs-search" onsubmit="return false;" class="search search-item">
	<input type="text" placeholder="{$aLang.blogs_search_title_hint}" autocomplete="off" name="blog_title" class="input-text" value="" onkeyup="ls.timer.run(ls.blog.searchBlogs,'blogs_search',['form-blogs-search'],1000);">
</form>

<div id="blogs-list-search" style="display:none;"></div>

<div id="blogs-list-original">
	{router page='blogs' assign=sBlogsRootPage}
	{include file='blog_list.tpl' bBlogsUseOrder=true sBlogsRootPage=$sBlogsRootPage}
	{include file='paging.tpl' aPaging=$aPaging}
</div>

{include file='footer.tpl'}