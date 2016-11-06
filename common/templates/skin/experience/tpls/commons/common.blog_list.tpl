{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

<script>
    //noinspection JSUnresolvedFunction
    $(function () {
        //noinspection JSUnresolvedFunction
        $('.blog-more i').tooltip({ html: true, delay: { show: 0, hide: 200 }});
    })
</script>
<div class="panel panel-default panel-table raised">

    <div class="panel-body">
        {if $aBlogs}
            <table class="blogs-table">
                {if $bBlogsUseOrder}
                    <thead>
                    <tr>
                        <th colspan="2">
                            <a href="{$sBlogsRootPage}?order=blog_title&order_way={if $sBlogOrder=='blog_title'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                               class="link {if $sBlogOrder=='blog_title'}{$sBlogOrderWay}{/if}">{$aLang.blogs_title}</a>
                        </th>

                        {if E::IsUser() && Router::GetActionEvent()!='personal'}
                            <th class="hidden-xxs">{$aLang.blog_join_leave}</th>
                        {/if}

                        {if Router::GetActionEvent()!='personal'}
                            <th class="hidden-xxs">
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
                        <th colspan="2">{$aLang.blogs_title}</th>
                        {if E::IsUser() && Router::GetActionEvent()!='personal'}
                            <th class="hidden-xxs">{$aLang.blog_join_leave}</th>
                        {/if}
                        {if Router::GetActionEvent()!='personal'}
                            <th class="hidden-xxs">{$aLang.blogs_readers}</th>
                        {/if}
                        {hook run='blog_list_header' sBlogsRootPage=$sBlogsRootPage sBlogOrder=$sBlogOrder sBlogOrderWayNext=$sBlogOrderWayNext sBlogOrderWay=$sBlogOrderWay}
                    </tr>
                    </thead>
                {/if}

                <tbody>

                {foreach $aBlogs as $oBlog}
                {$oUserOwner=$oBlog->getOwner()}
                {$oBlogType=$oBlog->getBlogType()}
                <tbody>
                <tr class="first-row">
                    <td class="blog-logo last-td wat" rowspan="2">
                        <div class="blog-logo">
                            {if Router::GetActionEvent()=='personal'}
                                <a href="{$oUserOwner->getProfileUrl()}">
                                    <img src="{$oUserOwner->getAvatarUrl('medium')}" {$oUserOwner->getAvatarImageSizeAttr('medium')} alt="{$oUserOwner->getDisplayName()}"/>
                                </a>
                            {else}
                                {$sBlogAvatar = $oBlog->getUrlFull()}
                                {if $sBlogAvatar}
                                    <a href="{$oBlog->getUrlFull()}">
                                        <img src="{$oBlog->getAvatarUrl('medium')}" {$oBlog->getAvatarImageSizeAttr('medium')} class="avatar"/>
                                    </a>
                                {else}
                                    <i class="fa fa-folder-o"></i>
                                {/if}
                            {/if}
                        </div>
                        <div class="blog-more">
                            {*{if Router::GetActionEvent()!='personal'}*}
                            <i class="fa fa-caret-down" data-toggle="tooltip" data-placement="bottom" data-original-title='
                                                <dl class="dl-horizontal">
                                                    <dt>{$aLang.infobox_blog_create}</dt><dd>{date_format date=$oBlog->getDateAdd() format="j F Y"}</dd>
                                                </dl>

                                                <dl class="dl-horizontal">
                                                    <dt>{$aLang.infobox_blog_topics}:</dt><dd>{$oBlog->getCountTopic()}</dd>
                                                    <dt>{$aLang.infobox_blog_users}:</dt><dd>{$oBlog->getCountUser()}</dd>
                                                    {hook run="blog_infobox" oBlog=$oBlog}
                                                </dl>

                                                {*<dl class="dl-horizontal">*}
                                                    {*<dt>Последний пост:</dt><dd><a href="#">Современный дизайн</a></dd>*}
                                                {*</dl>*}

                                                <a href="{$oBlog->getUrlFull()}">{$aLang.blog_read}</a>
                                                <a href="{router page='rss'}blog/{$oBlog->getUrl()}/">RSS</a>'></i>
                            {*{/if}*}
                        </div>
                    </td>
                    <td class="blog-name last-td wat">
                        <a href="#">
                            <a href="{$oBlog->getUrlFull()}" class="blog-name">{$oBlog->getTitle()|escape:'html'}</a>
                            {if $oBlogType}
                                {if $oBlogType->IsHidden()}
                                    <span title="{$aLang.blog_closed}" class="fa fa-eye-slash"></span>
                                {elseif $oBlogType->IsPrivate()}
                                    <span title="{$aLang.blog_closed}" class="fa fa-lock"></span>
                                {/if}
                            {/if}
                        </a>
                        <div class="blog-description">
                            {$oBlog->getDescription()|strip_tags|trim|truncate:150:'...'|escape:'html'}
                        </div>

                        {if (E::UserId() != $oBlog->getOwnerId()) && $oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE)}
                            <script>
                                $(function(){
                                    ls.lang.load({lang_load name="ex_blog_leave,ex_blog_join"});
                                })
                            </script>
                            <div class="visible-xxs pal0 hif last-div rating-value" style="display: none;">
                                <a href="#"  onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;"
                                   class="link link-dark link-lead">
                                    {if $oBlog->getUserIsJoin()}
                                        {$aLang.ex_blog_leave}
                                    {else}
                                        {$aLang.ex_blog_join}
                                    {/if}
                                </a>
                            </div>
                        {/if}
                        <div class="visible-xxs hif last-div" style="display: none;">
                            {$aLang.blogs_readers}: {$oBlog->getCountUser()}
                        </div>
                        {hook run='blog_list_line' oBlog=$oBlog}

                    </td>
                    {if Router::GetActionEvent()!='personal'}
                        <td class="blog-options hidden-xxs last-td">
                            {if (E::UserId() != $oBlog->getOwnerId()) && $oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE)}
                                <a href="#"  onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;"
                                   class="link {if $oBlog->getUserIsJoin()}link-dark{else}link-blue{/if} link-lead link-clear">
                                    {if $oBlog->getUserIsJoin()}
                                        {$aLang.ex_blog_leave}
                                    {else}
                                        {$aLang.ex_blog_join}
                                    {/if}
                                </a>
                            {else}
                                &mdash;
                            {/if}
                        </td>
                    {/if}
                    {if Router::GetActionEvent()!='personal'}
                        <td id="blog_user_count_{$oBlog->getId()}" class="blog-peoples hidden-xxs last-td">
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
