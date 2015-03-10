 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_profile.tpl"}

{block name="layout_vars" append}
    {$sMenuItemSelect="profile"}
{/block}

{block name="layout_profile_content"}

    <div class="panel panel-default panel-table panel-user-activity flat">

        <div class="panel-body">

            <h2 class="panel-header">{$aLang.profile_activity}</h2>

            <table class="table-profile-info">

                {if Config::Get('general.reg.invite') AND $oUserInviteFrom}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_invite_from}:</td>
                        <td>
                            <a href="{$oUserInviteFrom->getProfileUrl()}">{$oUserInviteFrom->getDisplayName()}</a>&nbsp;
                        </td>
                    </tr>
                {/if}

                {if Config::Get('general.reg.invite') AND $aUsersInvite}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_invite_to}:</td>
                        <td>
                            {foreach $aUsersInvite as $oUserInvite}
                                <a href="{$oUserInvite->getProfileUrl()}">{$oUserInvite->getDisplayName()}</a>
                                &nbsp;
                            {/foreach}
                        </td>
                    </tr>
                {/if}

                {if $aBlogsOwner}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_blogs_self}:</td>
                        <td>
                            {foreach $aBlogsOwner as $oBlog}
                                <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlog@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/if}

                {if $aBlogAdministrators}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_blogs_administration}:</td>
                        <td>
                            {foreach $aBlogAdministrators as $oBlogUser}
                                {$oBlog=$oBlogUser->getBlog()}
                                <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/if}

                {if $aBlogModerators}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_blogs_moderation}:</td>
                        <td>
                            {foreach $aBlogModerators as $oBlogUser}
                                {$oBlog=$oBlogUser->getBlog()}
                                <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/if}

                {if $aBlogUsers}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_blogs_join}:</td>
                        <td>
                            {foreach $aBlogUsers as $oBlogUser}
                                {$oBlog=$oBlogUser->getBlog()}
                                <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/if}

                {hook run='profile_whois_activity_item' oUserProfile=$oUserProfile}

                <tr>
                    <td class="text-muted cell-label">{$aLang.profile_date_registration}:</td>
                    <td>{date_format date=$oUserProfile->getDateRegister()}</td>
                </tr>

                {if $oSession}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_date_last}:</td>
                        <td>{date_format date=$oSession->getDateLast()}</td>
                    </tr>
                {/if}
            </table>

        </div>

    </div>




{if $aUsersFriend}
    <div class="panel panel-default user-friends sidebar flat">

        <div class="panel-body">

            <h2 class="panel-header">
                <a href="{$oUserProfile->getProfileUrl()}friends/" class="user-friends link link-lead link-clear link-dark">{$aLang.profile_friends}</a>
            </h2>

            {include file='commons/common.user_list_avatar.tpl' aUsersList=$aUsersFriend}
        </div>
        <div class="panel-footer">
            <a href="{$oUserProfile->getProfileUrl()}friends/" class="link link-dual link-lead link-clear">
                <i class="fa fa-users"></i>Все друзья ({$iCountFriendsUser})
            </a>
        </div>
    </div>

{/if}

{hook run='profile_whois_item_end' oUserProfile=$oUserProfile}


{/block}