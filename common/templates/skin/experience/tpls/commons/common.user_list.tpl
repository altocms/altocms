 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<script>

    //noinspection JSUnresolvedFunction
    $(function () {
        $('.action-mail a, .action-user a, .action-favourite a, .user-name span').tooltip();
        ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
    })
</script>
<div class="panel panel-default panel-table raised">
    <div class="panel-body">
        {if $aUsersList}
        <table class="peoples-table">
            {if $bUsersUseOrder}
                <thead>
                <tr>
                    <th colspan="3">
                        <a href="{$sUsersRootPage}?order=user_login&order_way={if $sUsersOrder=='user_login'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
                               {if $sUsersOrder=='user_login'}class="{$sUsersOrderWay}"{/if}>{$aLang.user}
                        </a>
                    </th>
                    <th class="hidden-xxs">
                        {$aLang.user_date_last}
                    </th>
                    {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
                </tr>
                </thead>
            {else}
                <thead>
                <tr>
                    <th colspan="3">{$aLang.user}</th>
                    <th class="hidden-xxs">{$aLang.user_date_last}</th>
                    {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
                </tr>
                </thead>
            {/if}


                {foreach $aUsersList as $oUserList}
                    {$oSession=$oUserList->getSession()}
                    {$oUserNote=$oUserList->getUserNote()}
                    {$oGeoTarget=$oUserList->getGeoTarget()}
            <tbody>
            <tr class="first-row">
                <td class="action-icon action-mail">
                    {if $oUserCurrent &&  $oUserList && $oUserList->getId() != $oUserCurrent->getId()}
                    <a href="{router page='talk'}add/?talk_users={$oUserList->getLogin()}"
                       data-toggle="tooltip" data-placement="right" data-original-title="{$aLang.send_message}">
                        <i class="fa fa-envelope"></i>
                    </a>
                    {/if}
                </td>
                <td rowspan="3" class="user-logo  last-td">
                    <a href="{$oUserList->getProfileUrl()}">
                        <img src="{$oUserList->getAvatarUrl('big')}" alt="{$oUserList->getDisplayName()}"/>
                    </a>
                </td>
                <td class="user-name {if !$oUserList->getProfileName()}no-realname{/if}">
                    <a href="{$oUserList->getProfileUrl()}"  style="font-weight: normal;" class="link link-lead link-blue link-clear">
                        {if $oUserNote}<i class="fa fa-globe"></i>&nbsp;
                        <span class="fa fa-comment-o cup"
                                          data-toggle="tooltip"

                                          data-placement="right"
                                          data-original-title="{$oUserNote->getText()|escape:'html'}"></span>&nbsp;{/if}
                        {$oUserList->getLogin()}
                    </a>

                </td>
                <td rowspan="3" class="hidden-xxs user-date-block  last-td">
                    <span>
                        {if $oSession}
                        <span class="user-date">{$oSession->getDateLast()|date_format:'d.m.Y'},</span>
                        <span class="user-time">{$oSession->getDateLast()|date_format:'H:i'}</span>
                        {/if}
                    </span>
                </td>
                {hook run='user_list_line' oUserList=$oUserList}
            </tr>
            <tr>
                <td class="action-icon action-user">

                    {if $oUserCurrent && $oUserList && $oUserCurrent && $oUserList->getId() != $oUserCurrent->getId()}
                        {if !$oUserList->getUserFriend()}
                            <a href="#modal-add_friend"
                               class="add_friend_button"
                               onclick="$('.add_friend_button').removeClass('selected'); $(this).addClass('selected'); return false;"
                               data-uid="{$oUserList->getId()}"
                               data-placement="right" data-original-title="{$aLang.user_friend_add}"
                               data-toggle="modal"><i class="fa fa-user"></i></a>
                        {elseif $oUserList->getUserFriend()->isFriend()}
                            <a href="#" class="small link link-light-gray link-clear link-lead" title="{$aLang.user_friend_del}"
                               data-placement="right" data-original-title="{$aLang.user_friend_del}"
                               onclick="return ls.user.removeFriend(this,{$oUserList->getId()},'del');">
                                <i class="fa fa-times-circle"></i>
                            </a>

                        {/if}
                    {/if}
                </td>
                <td class="user-name">{if $oUserList->getProfileName()}{$oUserList->getProfileName()}{/if}</td>
            </tr>
            <tr>
                <td class="action-icon action-favourite last-td">
                    {if $oUserCurrent && $oUserList &&  $oUserList->getId() != $oUserCurrent->getId()}
                        <a href="#"
                           onclick="ls.user.followToggleStar(this, {$oUserList->getId()}); return false;"
                           data-toggle="tooltip"
                           data-placement="right"
                           data-original-title="{if $oUserList->isFollow()}{$aLang.profile_user_unfollow}{else}{$aLang.profile_user_follow}{/if}">
                            <i class="fa {if $oUserList->isFollow()}fa-star{else}fa-star-half-full{/if}"></i>
                        </a>
                    {/if}
                </td>
                <td class="user-place last-td">
                    <div class="visible-xxs hif last-div user-date-block" style="display: none;">{$aLang.user_date_last}:
                    <span>
                        {if $oSession}
                            <span class="user-date">{$oSession->getDateLast()|date_format:'d.m.Y'},</span>
                            <span class="user-time">{$oSession->getDateLast()|date_format:'H:i'}</span>
                        {/if}
                    </span>
                    </div>
                    {hook run='user_list_linexxs' oUserList=$oUserList}
                    {if $oGeoTarget}
                        {if $oGeoTarget->getCountryId()}
                            <a class="link link-lead link-blue"
                               href="{router page='people'}country/{$oGeoTarget->getCountryId()}/">
                            {$oUserList->getProfileCountry()|escape:'html'}
                            </a>{if $oGeoTarget->getCityId()},{/if}
                        {/if}

                        {if $oGeoTarget->getCityId()}
                            <a class="link link-lead link-blue"
                               href="{router page='people'}city/{$oGeoTarget->getCityId()}/">
                                {$oUserList->getProfileCity()|escape:'html'}
                            </a>
                        {/if}
                    {/if}
                </td>
            </tr>
            </tbody>
                {/foreach}
        </table>
            {if $oUserCurrent}
                {include file='modals/modal.add_friend.tpl' oUserProfile=$oUserCurrent bUserList=true}
                <script>
                    $(function(){
                        ls.hook.add('ls_user_add_friend_after', function(){
                            $('a.selected').remove();
                        });
                    })
                </script>
            {/if}
        {else}
            {if $sUserListEmpty}
                <div class="bg-warning">{$sUserListEmpty}</div>
            {else}
                <div class="bg-warning">{$aLang.user_empty}</div>
            {/if}
        {/if}
    </div>
</div>

