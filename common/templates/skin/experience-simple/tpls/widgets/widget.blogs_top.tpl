 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}
 <script>
     $(function(){
         jQuery('.widget-blogs-top-items [data-alto-role="popover"]')
                 .altoPopover(false);
     })
 </script>

 <ul class="blogs-list widget-blogs-top-items">
     {foreach $aBlogs as $oBlog}
         <li data-alto-role="popover"
             data-type="blog"
             data-api="blog/{$oBlog->getId()}/info"
             data-placement="auto top"
             data-selector="type-blog"
             data-cache="false">
             <a href="{$oBlog->getUrlFull()}" class="blog-name link link-dual link-lead link-clear">
                <span class="blog-line-image">
                    {$sPath = $oBlog->getAvatarPath('small')}
                    {if $sPath}
                        <img src="{$oBlog->getAvatarPath('small')}" class="avatar uppercase"/>
                {else}
                    <i class="fa fa-folder"></i>
                    {/if}
                </span>

                 <span class="blog-line-title">{$oBlog->getTitle()|escape:'html'}</span>
             </a>
         </li>
     {/foreach}
 </ul>