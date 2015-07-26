 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

 {extends file="_index.tpl"}

 {block name="layout_vars"}
     {$menu="topics"}
 {/block}

 {block name="layout_content"}
     {$oUserOwner=$oBlog->getOwner()}
     {$oVote=$oBlog->getVote()}

     <script type="text/javascript">
         jQuery(function ($) {
             ls.lang.load({lang_load name="fa_blog_fold_info,fa_blog_expand_info"});
         });
     </script>

     {include_once file='modals/modal.blog_delete.tpl'}


     <!-- Блок топика -->
     <div class="panel panel-default user-info flat">
         <div class="panel-body">

             <div class="row user-info-block">
                 <div class="col-lg-20">
                     {$oBlogType=$oBlog->getBlogType()}
                     <img class="user-logo bor100" src="{$oBlog->getAvatarPath('large')}"  alt="avatar"/>
                     <div class="user-name">
                         <div class="user-login-block">
                            <span class="user-login">
                                {$oBlog->getTitle()|escape:'html'}
                                {if $oBlogType}
                                    {if $oBlogType->IsHidden()}
                                        <span title="{$aLang.blog_closed}" class="fa fa-eye-slash"></span>
                                    {elseif $oBlogType->IsPrivate()}
                                        <span title="{$aLang.blog_closed}" class="fa fa-lock"></span>
                                    {/if}
                                {/if}
                            </span>
                         </div>
                         <div class="user-full-name">
                            <span>
                                <span id="blog_user_count_{$oBlog->getId()}">{$iCountBlogUsers}</span> {$iCountBlogUsers|declension:$aLang.reader_declension:$sLang}
                                ,
                                {$oBlog->getCountTopic()} {$oBlog->getCountTopic()|declension:$aLang.topic_declension:$sLang}
                            </span>
                             {if E::IsUser() AND (E::UserId()==$oBlog->getOwnerId() OR E::IsAdmin() OR $oBlog->getUserIsAdministrator() OR $oBlog->getUserIsModerator())}

                             {/if}
                         </div>
                     </div>
                 </div>
                 {hook run="blog_header" oBlog=$oBlog}

             </div>

             <div class="user-more-block" id="blog-more-content" style="display: none;">
                 <div class="bg-warning user-about">
                     {$oBlog->getDescription()}
                 </div>

                 <div class="row user-more">
                     {hook run='blog_info_begin' oBlog=$oBlog}

                     <div class="col-md-12">
                         <table class="table">
                             <tbody>
                             <tr>
                                 <td class="bot0">{$aLang.infobox_blog_create}:</td>
                                 <td class="bot0">{date_format date=$oBlog->getDateAdd() format="j F Y"}</td>
                             </tr>
                             <tr>
                                 <td>{$aLang.infobox_blog_topics}:</td>
                                 <td>{$oBlog->getCountTopic()}</td>
                             </tr>
                             <tr>
                                 <td><a href="{$oBlog->getUrlFull()}users/">{$aLang.infobox_blog_users}</a></td>
                                 <td>{$iCountBlogUsers}</td>
                             </tr>
                             {hook run="blog_stat" oBlog=$oBlog}
                             </tbody>
                         </table>
                     </div>
                     <div class="col-md-12">
                         <table class="table">
                             <tbody>
                             <tr>
                                 <td class="bot0">{$aLang.blog_user_administrators} ({$iCountBlogAdministrators}):</td>
                                 <td class="bot0">
                                <span class="avatar">
                                    <a data-alto-role="popover"
                                       data-api="user/{$oUserOwner->getId()}/info"
                                       class="link link-clear" href="{$oUserOwner->getProfileUrl()}">
                                        <img src="{$oUserOwner->getAvatarUrl('mini')}" class="bor32" alt="avatar"/>
                                    </a>
                                    <a href="{$oUserOwner->getProfileUrl()}">{$oUserOwner->getDisplayName()}</a>
                                </span>
                                     {if $aBlogAdministrators}
                                         {foreach $aBlogAdministrators as $oBlogUser}
                                             {$oUser=$oBlogUser->getUser()}
                                             <br/>
                                             <span class="user-avatar">
                                            <a class="link link-clear" href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl('mini')}"  class="bor32" alt="avatar"/></a>
                                            <a data-alto-role="popover"
                                               data-api="user/{$oUser->getId()}/info"
                                               href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                        </span>
                                         {/foreach}
                                     {/if}
                                 </td>
                             </tr>
                             <tr>
                                 <td>
                                     {$aLang.blog_user_moderators} ({$iCountBlogModerators}):
                                 </td>
                                 <td>
                                     {if $aBlogModerators}
                                         {foreach $aBlogModerators as $oBlogUser}
                                             {$oUser=$oBlogUser->getUser()}
                                             <span class="user-avatar">
                                                <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl('mini')}"  class="bor32" alt="avatar"/></a>
                                                <a data-alto-role="popover"
                                                   data-api="user/{$oUser->getId()}/info"
                                                   data-api-param-tpl="default"
                                                   data-trigger="hover"
                                                   data-placement="top"
                                                   data-animation="true"
                                                   data-cache="true"
                                                   href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                            </span>
                                         {/foreach}
                                     {else}
                                         <span class="text-muted">{$aLang.blog_user_moderators_empty}</span>
                                     {/if}
                                 </td>
                             </tr>
                             </tbody>
                         </table>

                         {hook run='blog_info_end' oBlog=$oBlog}
                     </div>
                 </div>
             </div>


         </div>
         <div class="panel-footer par0">
             <ul>
                 <li><a href="{router page='rss'}blog/{$oBlog->getUrl()}/"
                        class="link link-light-gray link-clear link-lead" >
                         <i class="fa fa-rss"></i>&nbsp;RSS
                     </a></li>

                 {if E::UserId()!=$oBlog->getOwnerId() && $oBlog->getType()!='personal'}
                     <li><a href="#"
                            class="link link-light-gray link-clear link-lead"
                            onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;"
                            data-button-additional="button-blog-join-second-{$oBlog->getId()}" data-only-text="1"
                            id="button-blog-join-first-{$oBlog->getId()}">
                             {if $oBlog->getUserIsJoin()}{$aLang.blog_leave}{else}{$aLang.blog_join}{/if}
                         </a></li>
                 {/if}
                 <li class="pull-right pa0">
                     <a href="#"
                        id="blog-more"
                        onclick="ls.blog.toggleInfo(); return false;"
                        class="link link-light-gray link-lead link-clear btn btn-gray">
                         {$aLang.fa_blog_expand_info}
                     </a>
                 </li>
                 {if E::IsUser() AND ($oBlog->CanEditedBy(E::User()) OR $oBlog->CanAdminBy(E::User()) OR $oBlog->CanDeletedBy(E::User()))}
                     <li class="pull-right topic-controls">
                         {if $oBlog->CanAdminBy(E::User())}
                             <a href="{router page='blog'}admin/{$oBlog->getId()}/"
                                title="{$aLang.blog_menu_admin}"
                                class="small link link-lead link-dark link-clear">
                                 {*{$aLang.blog_menu_admin}*}
                                 <i class="fa fa-cog"></i>
                             </a>
                         {/if}
                         {if $oBlog->CanEditedBy(E::User())}
                             &nbsp;&nbsp;
                             <a href="{router page='blog'}edit/{$oBlog->getId()}/"
                                title="{$aLang.blog_menu_edit}"
                                class="small link link-lead link-dark link-clear right-border">
                                 {*{$aLang.blog_menu_edit}*}
                                 <i class="fa fa-pencil"></i>
                             </a>
                         {/if}
                         {if $oBlog->CanDeletedBy(E::User())}
                             &nbsp;&nbsp;
                             <a href="#" title="{$aLang.blog_menu_delete}"
                                class="small link link-lead link-clear link-red-blue js-modal-blog_delete">
                                 {*{$aLang.blog_menu_delete}*}
                                 <i class="fa fa-trash-o"></i>
                             </a>
                         {/if}
                     </li>
                 {/if}
             </ul>
         </div>

     </div>

     {hook run='blog_info' oBlog=$oBlog}
     <script>
         $(function(){
             $('.blog-submenu ul a').addClass('hvr-leftline-reveal');
         })
     </script>
     <div class="blog-submenu">
         <a class="btn btn-default {if $sMenuSubItemSelect=='good'}active{/if}"
            href="{$sMenuSubBlogUrl}newall/">{$aLang.blog_menu_collective_good}
         </a>
         <a class="btn btn-default {if $sMenuSubItemSelect=='new'}active{/if}"
            href="{$sMenuSubBlogUrl}newall/">
             {$aLang.blog_menu_collective_new}{if $iCountTopicsBlogNew>0} +{$iCountTopicsBlogNew}{/if}
         </a>
         <div class="inb outline-no dropdown {if $sMenuSubItemSelect=='discussed'} active{/if}">
             <a href="{$sMenuSubBlogUrl}discussed/" class="outline-no btn btn-default dropdown-toggle {if $sMenuSubItemSelect=='discussed'} active{/if}" data-toggle="dropdown">
                 {$aLang.blog_menu_collective_discussed}
                 <b class="caret"></b>
             </a>

             <ul class="dropdown-menu light">
                 <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='1'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}discussed/?period=1">{$aLang.blog_menu_top_period_24h}</a>
                 </li>
                 <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='7'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}discussed/?period=7">{$aLang.blog_menu_top_period_7d}</a>
                 </li>
                 <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='30'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}discussed/?period=30">{$aLang.blog_menu_top_period_30d}</a>
                 </li>
                 <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='all'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}discussed/?period=all">{$aLang.blog_menu_top_period_all}</a>
                 </li>
             </ul>
         </div>
         {if C::Get('rating.enabled')}
         <div class="inb outline-no dropdown{if $sMenuSubItemSelect=='top'} active{/if}">
             <a href="{$sMenuSubBlogUrl}top/" class="outline-no btn btn-default dropdown-toggle" data-toggle="dropdown">
                 {$aLang.blog_menu_collective_top}
                 <b class="caret"></b>
             </a>

             <ul class="dropdown-menu light">
                 <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='1'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}top/?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                 <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='7'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}top/?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                 <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='30'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}top/?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                 <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='all'}class="active"{/if}><a
                             href="{$sMenuSubBlogUrl}top/?period=all">{$aLang.blog_menu_top_period_all}</a></li>
             </ul>

             {hook run='menu_blog_blog_item'}
         </div>
         {/if}
     </div>

     {if $bCloseBlog}
         <div class="bg-warning">
             {$aLang.blog_close_show}
         </div>
     {else}
         {include file='topics/topic.list.tpl'}
     {/if}

 {/block}
