 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

 {extends file="_index.tpl"}

 {block name="layout_content"}
     <div class="action-header">
         {include file='menus/menu.talk.tpl'}
     </div>
     {$oUser=$oTalk->getUser()}

     <div class="panel panel-default topic flat">

         <div class="panel-body">
             <h3 class="topic-title accent">
                 {$oTalk->getTitle()|escape:'html'}
             </h3>

             <div class="topic-header-info">

             </div>

             <div class="topic-text">
                 {$oTalk->getText()}
             </div>

             {*{include file='actions/talk/action.talk.speakers.tpl'}*}

         </div>

         <div class="topic-footer">
             <ul class="text-muted list-unstyled list-inline topic-footer-info">
                 <li data-alto-role="popover"
                     data-api="user/{$oUser->getId()}/info"
                     class="topic-user">
                     <img src="{$oUser->getAvatarUrl('small')}" alt="{$oUser->getDisplayName()}" class="top0"/>
                     <a class="userlogo link link-dual link-lead link-clear" href="{$oUser->getProfileUrl()}">
                         {$oUser->getDisplayName()}
                     </a>
                 </li>
                 <li class="topic-info-date">
                     <span class="small topic-date">{$oTalk->getDate()|date_format:'d.m.Y'}</span>,&nbsp;
                     <span class="small topic-time">{$oTalk->getDate()|date_format:"H:i"}</span>
                 </li>
                 <li class="topic-info-favourite"><a href="#"
                                                     onclick="return ls.favourite.toggle({$oTalk->getId()},this,'talk');"
                                                     class="link link-light-gray link-lead link-clear  {if $oTalk->getIsFavourite()}active{/if}">
                         {if $oTalk->getIsFavourite()}<i class="fa fa-star"></i>{else}<i class="fa fa-star-o"></i>{/if}
                 </li>

                 <li class="delete pull-right marr0 hidden-xs visible-sm visible-lg visible-sm">
                     <a href="#"
                        onclick="return ls.talk.removeMessage('{$oTalk->getId()}');"
                        class="link link-lead link-red-blue link-clear">
                         {$aLang.delete}
                     </a>
                 </li>
                 <li class="delete pull-right marr0 visible-xs hidden-md hidden-sm hidden-lg">
                     <a href="#"
                        onclick="return ls.talk.removeMessage('{$oTalk->getId()}');"
                        class="link link-lead link-red-blue link-clear">
                         <i class="fa fa-times"></i>
                     </a>
                 </li>

                 {*{if $oTalk->getUserId()==E::UserId() OR E::IsAdmin()}*}
                 {*<li class="delete pull-right marr0 hidden-xs visible-sm visible-lg visible-sm">*}
                 {*<a href="#" class="link link-light-gray link-lead link-clear " onclick="jQuery('#talk_recipients').toggle(); return false;">&nbsp;*}
                 {*{$aLang.talk_speaker_edit}*}
                 {*</a>*}
                 {*</li>*}
                 {*<li class="delete pull-right marr0 visible-xs hidden-md hidden-sm hidden-lg">*}
                 {*<a href="#" class="link link-light-gray link-lead link-clear " onclick="jQuery('#talk_recipients').toggle(); return false;">&nbsp;*}
                 {*<i class="fa fa-pencil"></i>*}
                 {*</a>*}
                 {*</li>*}


                 {*{/if}*}

                 {hook run='talk_read_info_item' talk=$oTalk}
             </ul>
         </div>

     </div>

     {$oTalkUser=$oTalk->getTalkUser()}

     {if !$bNoComments}
         {include
         file='comments/comment.tree.tpl'
         iTargetId=$oTalk->getId()
         sTargetType='talk'
         iCountComment=$oTalk->getCountComment()
         sDateReadLast=$oTalkUser->getDateLast()
         sNoticeCommentAdd=$aLang.topic_comment_add
         bNoCommentFavourites=true}
     {/if}

 {/block}
