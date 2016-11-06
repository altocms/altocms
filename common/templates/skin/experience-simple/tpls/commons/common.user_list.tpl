 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<script>

    //noinspection JSUnresolvedFunction
    $(function () {
        $('.action-mail a, .action-user a, .action-favourite a, .user-name span').tooltip();
        ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
    })
</script>
<div class="panel panel-default panel-table flat">
    <div class="panel-body">
        {if $aUsersList}
        <table class="peoples-table">
            {if $bUsersUseOrder}
                <thead>
                <tr>
                    <th colspan="2">
                        <a href="{$sUsersRootPage}?order=user_login&order_way={if $sUsersOrder=='user_login'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
                           class="link link-dark link-dark-dotted {if $sUsersOrder=='user_login'}{$sUsersOrderWay}{/if}">{$aLang.user}
                        </a>
                    </th>
                    <th>
                        {$aLang.user_date_last}
                    </th>
                    {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
                </tr>
                </thead>
            {else}
                <thead>
                <tr>
                    <th colspan="2">{$aLang.user}</th>
                    <th>{$aLang.user_date_last}</th>
                    {hook run='user_list_header' bUsersUseOrder=$bUsersUseOrder sUsersRootPage=$sUsersRootPage sUsersOrderWay=$sUsersOrderWay sUsersOrder=$sUsersOrder}
                </tr>
                </thead>
            {/if}


            {foreach $aUsersList as $oUserList}
                {$oSession=$oUserList->getSession()}
                {$oUserNote=$oUserList->getUserNote()}
                {$oGeoTarget=$oUserList->getGeoTarget()}
                <tbody>
                    <tr>
                        <td class="user-logo">
                            <a href="{$oUserList->getProfileUrl()}">
                                <img src="{$oUserList->getAvatarUrl('small')}" {$oUserList->getAvatarImageSizeAttr('small')} alt="{$oUserList->getDisplayName()}"/>
                            </a>
                        </td>
                        <td class="user-name {if !$oUserList->getProfileName()}no-realname{/if}">
                            <a data-alto-role="popover"
                               data-api="user/{$oUserList->getId()}/info"
                               data-api-param-tpl="default"
                               data-trigger="hover"
                               data-placement="right"
                               data-animation="true"
                               data-cache="true"
                               href="{$oUserList->getProfileUrl()}"  style="font-weight: normal;" class="link link-lead link-blue link-clear">
                                {if $oUserNote}<i class="fa fa-globe"></i>&nbsp;
                                <span class="fa fa-comment-o cup"
                                                  data-toggle="tooltip"

                                                  data-placement="right"
                                                  data-original-title="{$oUserNote->getText()|escape:'html'}"></span>&nbsp;{/if}
                                {$oUserList->getLogin()}
                            </a>

                        </td>
                        <td class="user-date-block">
                            <span>
                                {if $oSession}
                                <span class="user-date">{$oSession->getDateLast()|date_format:'d.m.Y'},</span>
                                <span class="user-time">{$oSession->getDateLast()|date_format:'H:i'}</span>
                                {/if}
                            </span>
                        </td>
                        {hook run='user_list_line' oUserList=$oUserList}
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

