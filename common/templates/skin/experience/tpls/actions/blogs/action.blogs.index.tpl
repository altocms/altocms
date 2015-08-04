 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$sMenuHeadItemSelect="blogs"}
{/block}

{block name="layout_pre_content"}
    <div class="panel panel-default panel-search raised">

        <div class="panel-body">

            <div class="panel-header">
                {$aLang.blogs}
            </div>

            <form action="" method="POST" id="form-blogs-search" onsubmit="return false;" class="search-item">
                <label>
                    <input type="text" placeholder="{$aLang.blogs_search_title_hint}" autocomplete="off" name="blog_title"
                           class="form-control" value=""
                           onkeyup="ls.timer.run('blog-search', ls.blog.searchBlogs, ['#form-blogs-search'], 1.5);">
                </label>
            </form>

        </div>

        <div class="panel-footer">
            <a class="link link-light-gray link-lead link-clear {if Router::GetActionEvent()!='personal'}active{/if}" href="{router page='blogs'}">{$aLang.all_blogs}</a>
            <a class="link link-light-gray link-lead link-clear {if Router::GetActionEvent()=='personal'}active{/if}" href="{router page='blogs'}personal">{$aLang.my_blogs}</a>
            {*<a class="link link-light-gray link-lead link-clear" href="#">Мои</a>*}
        </div>

    </div>
{/block}

{block name="layout_content"}
    <div class="content-inner-panel">
        <div id="blogs-list-search" style="display:none;"></div>
        <div id="blogs-list-original">
            {*{router page='blogs' assign=sBlogsRootPage}*}
            {include file='commons/common.blog_list.tpl' bBlogsUseOrder=true}
        </div>
    </div>
    <div class="content-inner-paging">
        {include file='commons/common.pagination.tpl' aPaging=$aPaging}
    </div>
{/block}
