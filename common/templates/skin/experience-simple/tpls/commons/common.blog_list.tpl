{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<script>
    //noinspection JSUnresolvedFunction
    $(function () {
        //noinspection JSUnresolvedFunction
        $('.blog-more i').tooltip({ html: true, delay: { show: 0, hide: 200 }});
    })
</script>
<div class="panel panel-default panel-table flat">

    <div class="panel-body">
        {if $aBlogs}
            <table class="blogs-table">
                {if $bBlogsUseOrder}
                    <thead>
                    <tr>
                        <th>
                            <a href="{$sBlogsRootPage}?order=blog_title&order_way={if $sBlogOrder=='blog_title'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                               class="link {if $sBlogOrder=='blog_title'}{$sBlogOrderWay}{/if}">{$aLang.blogs_title}</a>
                        </th>

                        {if E::IsUser() && Router::GetActionEvent()!='personal'}
                            <th>{$aLang.blog_join_leave}</th>
                        {/if}

                        {if Router::GetActionEvent()!='personal'}
                            <th>
                                <a href="{$sBlogsRootPage}?order=blog_count_user&order_way={if $sBlogOrder=='blog_count_user'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                                   class="link {if $sBlogOrder=='blog_count_user'}{$sBlogOrderWay}{/if}">{$aLang.blogs_readers}</a>
                            </th>
                        {/if}
                        {hook run='blog_list_header' bBlogsUseOrder=$bBlogsUseOrder sBlogsRootPage=$sBlogsRootPage sBlogOrder=$sBlogOrder sBlogOrderWayNext=$sBlogOrderWayNext sBlogOrderWay=$sBlogOrderWay}
                    </tr>
                    </thead>
                {else}
                    <thead>
                    <tr>
                        <th>{$aLang.blogs_title}</th>
                        {if E::IsUser() && Router::GetActionEvent()!='personal'}
                            <th>{$aLang.blog_join_leave}</th>
                        {/if}
                        {if Router::GetActionEvent()!='personal'}
                            <th>{$aLang.blogs_readers}</th>
                        {/if}
                        {hook run='blog_list_header' sBlogsRootPage=$sBlogsRootPage sBlogOrder=$sBlogOrder sBlogOrderWayNext=$sBlogOrderWayNext sBlogOrderWay=$sBlogOrderWay}
                    </tr>
                    </thead>
                {/if}

                {foreach $aBlogs as $oBlog}
                {$oUserOwner=$oBlog->getOwner()}
                {$oBlogType=$oBlog->getBlogType()}
                <tbody>
                <tr>
                    <td>
                        <div class="blog-name-container">
                            <a data-alto-role="popover"
                               data-type="blog"
                               data-api="blog/{$oBlog->getId()}/info"
                               data-placement="auto top"
                               data-selector="type-blog"
                               data-cache="false"
                               href="{$oBlog->getUrlFull()}" class="blog-name">
                                {if Router::GetActionEvent()=='personal'}
                                    <img src="{$oUserOwner->getAvatarUrl('medium')}" class="mar6" alt="{$oUserOwner->getDisplayName()}"/>
                                {else}
                                    {$sBlogAvatar = $oBlog->getUrlFull()}
                                    {if $sBlogAvatar}
                                        <img src="{$oBlog->getAvatarPath('medium')}" width="48" height="48" class="avatar mar6"/>
                                    {else}
                                        <i class="fa fa-folder-o mar6"></i>
                                    {/if}
                                {/if}
                                {$oBlog->getTitle()|escape:'html'}
                            </a>
                            {if $oBlogType}
                                {if $oBlogType->IsHidden()}
                                    <span title="{$aLang.blog_closed}" class="fa fa-eye-slash"></span>
                                {elseif $oBlogType->IsPrivate()}
                                    <span title="{$aLang.blog_closed}" class="fa fa-lock"></span>
                                {/if}
                            {/if}
                        </div>
                    </td>
                    {if E::IsUser() && Router::GetActionEvent()!='personal'}
                    <td>
                        {if (E::UserId() != $oBlog->getOwnerId())}
                        {*{if (E::UserId() != $oBlog->getOwnerId()) && $oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE) || }*}
                            <script>
                                $(function(){
                                    ls.lang.load({lang_load name="ex_blog_leave,ex_blog_join"});
                                })
                            </script>
                            <div>
                                <a href="#"  onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;"
                                   class="link link-dark link-lead link-clear">
                                    {if $oBlog->getUserIsJoin()}
                                        {$aLang.ex_blog_leave}
                                    {else}
                                        {$aLang.ex_blog_join}
                                    {/if}
                                </a>
                            </div>
                        {else}
                            -
                        {/if}
                        {hook run='blog_list_line' oBlog=$oBlog}

                    </td>
                    {/if}
                    {if Router::GetActionEvent()!='personal'}
                        <td id="blog_user_count_{$oBlog->getId()}">
                            {$oBlog->getCountUser()}
                        </td>
                    {/if}
                    {hook run='blog_list_linexxs' oBlog=$oBlog}
                </tr>
                </tbody>
                {/foreach}

            </table>
        {else}
            {if $sBlogsEmptyList}
                <div class="bg-warning">{$sBlogsEmptyList}</div>
            {/if}
        {/if}
    </div>
</div>
