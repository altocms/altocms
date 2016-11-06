 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}
{if $oTalk}
 <div class="talk-search talk-recipients pab0">


         {if $oTalk->getUserId()==E::UserId() OR E::IsAdmin()}
         <h4>{$aLang.talk_speaker_title}&nbsp;&nbsp;&nbsp;
             <a href="#" class="link link-blue link-lead link-clear small" onclick="jQuery('#talk_recipients').toggle(); return false;">&nbsp;
                 {$aLang.talk_speaker_edit}
             </a></h4>
         {/if}



     {*<header class="talk-recipients-header">*}
     {*{foreach $oTalk->getTalkUsers() as $oTalkUser}*}
     {*{$oUserRecipient=$oTalkUser->getUser()}*}
     {*{if $oUser->getId() != $oUserRecipient->getId()}*}
     {*<a class="link link-lead link-clear {if $oTalkUser->getUserActive() != $TALK_USER_ACTIVE}inactive{/if}" href="{$oUserRecipient->getProfileUrl()}">{$oUserRecipient->getDisplayName()}</a>{if !$oTalkUser@last}, {/if}*}
     {*{/if}*}
     {*{/foreach}*}
     {*</header>*}



     {if $oTalk->getUserId()==E::UserId() OR E::IsAdmin()}
         <div class="talk-search-content talk-recipients-content">

             <form onsubmit="return ls.talk.addToTalk({$oTalk->getId()});"  style="display: none;"  id="talk_recipients">
                 <div class="form-group">
                     <div class="input-group">
                         <span class="input-group-addon">{$aLang.talk_speaker_add_label}</span>
                         <input type="text" id="talk_speaker_add" name="add" placeholder="{$aLang.panel_user_promt}" class="form-control autocomplete-users-sep" />

                     </div>
                 </div>

                 <input type="hidden" id="talk_id" value="{$oTalk->getId()}" />
             </form>

             <div id="speaker_list_block">
                 {if $oTalk->getTalkUsers()}
                     <ul class="list-unstyled text-muted list-inline" id="speaker_list">
                         {foreach $oTalk->getTalkUsers() as $oTalkUser}
                             {if $oTalkUser->getUserId()!=E::UserId()}
                                 {$oUser=$oTalkUser->getUser()}

                                 {if $oTalkUser->getUserActive()!=$TALK_USER_DELETE_BY_AUTHOR}
                                     <li data-alto-role="popover"
                                         data-api="user/{$oUser->getId()}/info"
                                         id="speaker_item_{$oTalkUser->getUserId()}_area">
                                         <img src="{$oUser->getAvatarUrl('small')}" {$oUser->getAvatarImageSizeAttr('small')} alt="{$oUser->getDisplayName()}"/>
                                         <a class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}" href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                         {if $oTalkUser->getUserActive()==$TALK_USER_ACTIVE}- <a href="#" id="speaker_item_{$oTalkUser->getUserId()}" class="link link-lead link-red-blue delete"><i class="fa fa-times"></i></a>{/if}
                                     </li>
                                 {/if}
                             {/if}
                         {/foreach}
                     </ul>
                 {/if}
             </div>
         </div>
     {/if}
 </div>
{/if}
