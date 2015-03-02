 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<ul class="blogs-list">
    {foreach $aBlogs as $oBlog}
    <li>
        <a href="{$oBlog->getUrlFull()}" class="blog-name link link-dual link-lead link-clear">
            {$sPath = $oBlog->getAvatarPath('32x32crop')}
            {if $sPath}
                <img src="{$oBlog->getAvatarPath('32x32crop')}?1" class="avatar uppercase"/>
            {else}
                <i class="fa fa-folder"></i>
            {/if}

            {$oBlog->getTitle()|escape:'html'}
        </a>
    </li>
    {/foreach}
</ul>