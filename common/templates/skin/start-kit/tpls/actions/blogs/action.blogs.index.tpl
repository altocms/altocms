{extends file="_index.tpl"}

{block name="layout_vars"}
    {$sMenuHeadItemSelect="blogs"}
{/block}

{block name="layout_content"}
    <div class="content-inner-panel">
        <div class="page-header">
            <div class=" header">{$aLang.blogs}</div>
        </div>
        <form action="" method="POST" id="form-blogs-search" onsubmit="return false;" class="search-item">
            <input type="text" placeholder="{$aLang.blogs_search_title_hint}" autocomplete="off" name="blog_title"
                   class="form-control" value=""
                   onkeyup="ls.timer.run('blog-search', ls.blog.searchBlogs, ['#form-blogs-search'], 1.5);">
        </form>

        <div class="panel panel-default flat">
            <ul class="nav nav-pills context-menu">
                <li class="bordered{if Router::GetActionEvent()!='personal'} active{/if}"><a href="{router page='blogs'}">{$aLang.all_blogs}</a></li>
                <li class="bordered{if Router::GetActionEvent()=='personal'} active{/if}"><a href="{router page='blogs'}personal">{$aLang.personal_blogs}</a></li>
            </ul>
            {*<li class="bordered"><a class="link link-light-gray link-lead link-clear" href="#">{$aLang.my_blogs}</a></li>*}
        </div>

        <div id="blogs-list-search" style="display:none;"></div>
        <div id="blogs-list-original">
            {*{router page='blogs' assign=sBlogsRootPage}*}
            {include file='commons/common.blog_list.tpl' bBlogsUseOrder=true}{*sBlogsRootPage=$sBlogsRootPage*}
        </div>
    </div>
    <div class="content-inner-paging">
        {include file='commons/common.pagination.tpl' aPaging=$aPaging}
    </div>
{/block}
