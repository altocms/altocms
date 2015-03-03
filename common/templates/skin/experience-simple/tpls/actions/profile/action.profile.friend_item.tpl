 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oUserFriend}
    {if $oUserFriend->isFriend()}
        <li>
            <a href="#" class="small link link-light-gray link-clear link-lead" title="{$aLang.user_friend_del}" onclick="return ls.user.removeFriend(this,{$oUserProfile->getId()},'del');">
                <i class="fa fa-user-times"></i>&nbsp;{$aLang.user_friend_del}
            </a>
        </li>
    {elseif $oUserFriend->AcceptionWait()}
        <li>
            <a href="#" class="small link link-light-gray link-clear link-lead" title="{$aLang.user_friend_add}" onclick="return ls.user.addFriend(this,{$oUserProfile->getId()},'accept');">
                <i class="fa fa-user-plus"></i>&nbsp;{$aLang.user_friend_add}
            </a>
        </li>
    {elseif $oUserFriend->RequestRejected()}
        <li class="small link link-light-gray link-clear link-lead">{$aLang.user_friend_offer_reject}</li>
    {elseif $oUserFriend->RequestSent()}
        <li class="small link link-light-gray link-clear link-lead">{$aLang.user_friend_offer_send}</li>
    {elseif $oUserFriend->isCancelled()}
        <li class="small link link-light-gray link-clear link-lead">{$aLang.user_friend_add_cancelled}</li>
    {elseif $oUserFriend->isDeleted()}
        <li class="small link link-light-gray link-clear link-lead">{$aLang.user_friend_add_deleted}</li>
    {/if}
{else}
    {include_once file="modals/modal.add_friend.tpl"}
    <li>
        <a  class="small link link-light-gray link-clear link-lead" href="#modal-add_friend" title="{$aLang.user_friend_add}" data-toggle="modal"><i class="fa fa-user-plus"></i>&nbsp;{$aLang.user_friend_add}</a>
    </li>
{/if}
