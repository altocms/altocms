<div class="popover-blog-info-container row">

   <div class="col-md-24"><div class="popover-header">{$oBlog->getTitle()}</div></div>

   <div class="col-md-9">
      <img width="100%" src="{$oBlog->getAvatarPath('huge')}" alt="{$oBlog->getTitle()}"/>
   </div>
   <div class="col-md-15">
      <table>
         <tbody>
         <tr>
            <td>{E::ModuleLang()->Get('infobox_blog_create')}:</td>
            <td class="text-right">{date_format date=$oBlog->getDateAdd() format="j F Y"}</td>
         </tr>
         <tr>
            <td>{E::ModuleLang()->Get('infobox_blog_topics')}</td>
            <td class="text-right">{$oBlog->getCountTopic()}</td>
         </tr>
         {if C::Get('rating.enabled')}
         <tr>
            <td>{E::ModuleLang()->Get('top')}</td>
            <td class="text-right">{$oBlog->getRating()}</td>
         </tr>
         {/if}
         <tr>
            <td><a href="{$oBlog->getUrlFull()}users/">{E::ModuleLang()->Get('infobox_blog_users')}</a></td>
            <td class="text-right">{$iCountBlogUsers}</td>
         </tr>
         <tr>
            <td>
               <a href="{$oBlog->getUrlFull()}">{E::ModuleLang()->Get('infobox_blog_url')}</a>
            </td>
            <td class="text-right">
               <a href="{router page='rss'}blog/{$oBlog->getUrl()}/"
                  class="link link-light-gray link-clear link-lead" >
                  <i class="fa fa-rss"></i>&nbsp;RSS
               </a>
            </td>
         </tr>
         </tbody>
      </table>
   </div>
</div>
<div class="blog-description-container">
    {$oBlog->getDescription()|strip_tags|trim|truncate:50:'...'|escape:'html'}
</div>

