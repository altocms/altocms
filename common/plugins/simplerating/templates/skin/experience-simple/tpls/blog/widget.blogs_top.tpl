<ul class="blogs-list">
    {foreach $aBlogs as $oBlog}
        <li class="js-popover-blog-{$oBlog->getId()}" data-placement="auto left">
            <a href="{$oBlog->getUrlFull()}" class="blog-name link link-dual link-lead link-clear">
                <span class="blog-line-image">
                    {$sUrl = $oBlog->getAvatarUrl('small')}
                    {if $sUrl}
                        <img src="{$sUrl}" class="avatar uppercase"/>
                    {else}
                        <i class="fa fa-folder"></i>
                    {/if}
                </span>

                <span class="blog-line-title">{$oBlog->getTitle()|escape:'html'}</span>
                <span class="blog-line-rating">{$oBlog->getRating()}</span>
            </a>
        </li>
    {/foreach}
</ul>