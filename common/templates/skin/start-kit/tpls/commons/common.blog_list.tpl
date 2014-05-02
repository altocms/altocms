<table class="table table-blogs">
    {if $bBlogsUseOrder}
        <thead>
        <tr>
            <th class="cell-name">
                <small>
                    <a href="{$sBlogsRootPage}?order=blog_title&order_way={if $sBlogOrder=='blog_title'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                       {if $sBlogOrder=='blog_title'}class="{$sBlogOrderWay}"{/if}><span>{$aLang.blogs_title}</span></a>
                </small>
            </th>

            {if E::IsUser()}
                <th class="cell-join">
                    <small>{$aLang.blog_join_leave}</small>
                </th>
            {/if}

            <th class="small cell-readers">
                <a href="{$sBlogsRootPage}?order=blog_count_user&order_way={if $sBlogOrder=='blog_count_user'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                   {if $sBlogOrder=='blog_count_user'}class="{$sBlogOrderWay}"{/if}><span>{$aLang.blogs_readers}</span></a>
            </th>
            <th class="cell-rating align-center">
                <small>
                    <a href="{$sBlogsRootPage}?order=blog_rating&order_way={if $sBlogOrder=='blog_rating'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
                       {if $sBlogOrder=='blog_rating'}class="{$sBlogOrderWay}"{/if}><span>{$aLang.blogs_rating}</span></a>
                </small>
            </th>
        </tr>
        </thead>
    {else}
        <thead>
        <tr>
            <th class="cell-name">
                <small>{$aLang.blogs_title}</small>
            </th>

            {if E::IsUser()}
                <th class="cell-join">
                    <small>{$aLang.blog_join_leave}</small>
                </th>
            {/if}

            <th class="cell-readers">
                <small>{$aLang.blogs_readers}</small>
            </th>
            <th class="cell-rating align-center">
                <small>{$aLang.blogs_rating}</small>
            </th>
        </tr>
        </thead>
    {/if}

    <tbody>
    {if $aBlogs}
        {foreach $aBlogs as $oBlog}
            {$oUserOwner=$oBlog->getOwner()}
            {$oBlogType=$oBlog->getBlogType()}
            <tr>
                <td class="cell-name">
                    <a href="{$oBlog->getUrlFull()}">
                        <img src="{$oBlog->getAvatarPath(48)}" width="48" height="48" class="avatar visible-lg"/>
                    </a>

                    <h4>
                        <a href="{$oBlog->getUrlFull()}" class="blog-name">{$oBlog->getTitle()|escape:'html'}</a>

                        {if $oBlog->getType() == 'close'}
                            <span title="{$aLang.blog_closed}" class="glyphicon glyphicon-lock text-muted"></span>
                        {/if}
                    </h4>

                    <p class="blog-description">{$oBlog->getDescription()|strip_tags|trim|truncate:150:'...'|escape:'html'}</p>
                </td>

                {if E::IsUser()}
                    <td class="small cell-join">
                        {if (E::UserId() != $oBlog->getOwnerId()) && $oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE)}
                            <a href="#" onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;"
                               class="link-dotted">
                                {if $oBlog->getUserIsJoin()}
                                    {$aLang.blog_leave}
                                {else}
                                    {$aLang.blog_join}
                                {/if}
                            </a>
                        {else}
                            &mdash;
                        {/if}
                    </td>
                {/if}

                <td class="small cell-readers" id="blog_user_count_{$oBlog->getId()}">{$oBlog->getCountUser()}</td>
                <td class="small text-success cell-rating">{$oBlog->getRating()}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td colspan="3">
                {if $sBlogsEmptyList}
                    {$sBlogsEmptyList}
                {else}

                {/if}
            </td>
        </tr>
    {/if}
    </tbody>
</table>
