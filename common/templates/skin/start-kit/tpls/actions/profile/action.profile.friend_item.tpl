{if $oUserFriend}
    {if $oUserFriend->isFriend()}
        <li>
            <a href="#" title="{$aLang.user_friend_del}" onclick="return ls.user.removeFriend(this,{$oUserProfile->getId()},'del');">
                {$aLang.user_friend_del}
            </a>
        </li>
    {elseif $oUserFriend->AcceptionWait()}
        <li>
            <a href="#" title="{$aLang.user_friend_add}" onclick="return ls.user.addFriend(this,{$oUserProfile->getId()},'accept');">
                {$aLang.user_friend_add}
            </a>
        </li>
    {elseif $oUserFriend->RequestRejected()}
        <li>{$aLang.user_friend_offer_reject}</li>
    {elseif $oUserFriend->RequestSent()}
        <li>{$aLang.user_friend_offer_send}</li>
    {elseif $oUserFriend->isCancelled()}
        <li>{$aLang.user_friend_add_cancelled}</li>
    {elseif $oUserFriend->isDeleted()}
        <li>{$aLang.user_friend_add_deleted}</li>
    {/if}
{else}
    {include_once file="modals/modal.add_friend.tpl"}
    <li>
        <a href="#modal-add_friend" title="{$aLang.user_friend_add}" data-toggle="modal">{$aLang.user_friend_add}</a>
    </li>
{/if}
