<ul class="list-unstyled item-list">
    {foreach $aBlogs as $oBlog}
        <li class="media">
            <a href="{$oBlog->getUrlFull()}" class="pull-left"><img src="{$oBlog->getAvatarPath(48)}" width="48"
                                                                    height="48" class="media-object avatar"/></a>

            <div class="media-body">
                <a href="{$oBlog->getUrlFull()}" class="blog-top">{$oBlog->getTitle()|escape:'html'}</a>

                <p class="small text-muted">{$aLang.blog_rating}: <strong>{$oBlog->getRating()}</strong></p>
            </div>
        </li>
    {/foreach}
</ul>
