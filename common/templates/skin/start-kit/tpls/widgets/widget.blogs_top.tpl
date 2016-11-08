<ul class="list-unstyled item-list">
    {foreach $aBlogs as $oBlog}
        <li class="media">
            <a href="{$oBlog->getUrlFull()}" class="pull-left">
                <img src="{$oBlog->getAvatarUrl('small')}" {$oBlog->getAvatarImageSizeAttr('small')} class="media-object avatar"/>
            </a>

            <div class="media-body">
                <a href="{$oBlog->getUrlFull()}" class="blog-top">{$oBlog->getTitle()|escape:'html'}</a>
            </div>
        </li>
    {/foreach}
</ul>
