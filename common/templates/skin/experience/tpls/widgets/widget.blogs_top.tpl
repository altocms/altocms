 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<ul class="blogs-list">
    {foreach $aBlogs as $oBlog}
        <li data-alto-role="popover"
            data-type="blog"
            data-api="blog/{$oBlog->getId()}/info"
            data-placement="auto left"
            data-cache="false">
        <a href="{$oBlog->getUrlFull()}" class="blog-name link link-dual link-lead link-clear">
            {$sPath = $oBlog->getAvatarUrl(24)}
            {if $sPath}
                <img src="{$oBlog->getAvatarUrl(24)}" width="24" height="24" class="avatar uppercase"/>
            {else}
                <i class="fa fa-folder"></i>
            {/if}

            {$oBlog->getTitle()|escape:'html'}
        </a>
    </li>
    {/foreach}
</ul>